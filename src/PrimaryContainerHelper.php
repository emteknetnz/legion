<?php

namespace Emteknetnz\Legion;

class PrimaryContainerHelper
{
    public function init(): void
    {
        $timeStart = microtime(true);
        $this->checkIsInsideDocker();

        $specifiedTestDir = $this->getSpecifiedTestDir();
        $baseDir = __DIR__ . "/../../../..";
        $testDir = "$baseDir/" . $specifiedTestDir;
        $logDir = __DIR__ . '/../testresults';
        $moduleDir = __DIR__ . '/..';
        $funcNames = $this->getTestFunctionNames($testDir);
        
        $this->createLogDir($logDir);
        $this->runTestsInsideSecondaryContainers($testDir, $funcNames, $moduleDir, $logDir);
        $this->waitForTestsToComplete();
        $this->removeSecondaryContainers($funcNames);
        $this->parseTestOutputs($logDir);
        $this->echoExecutionTime($timeStart);
    }

    protected function checkIsInsideDocker(): void
    {
        if (!file_exists('/home/is_legion_docker.txt')) {
            echo "This file should only be run from within the primary docker container_\n";
            die;
        }
    }

    protected function getSpecifiedTestDir(): string
    {
        $argv = $_SERVER['argv'];
        if (count($argv) < 2) {
            echo "Please specify a test directory\n";
            die;
        }
        return $argv[1];
    }

    /**
     * Will spin up a new webserver_secondary that will be removed after phpunit is run from inside of it
     * TODO: return an array of container ID's and pass that to waitForTestsToComplete()
     */
    protected function runTestsInsideSecondaryContainers(
        string $testDir,
        array $funcNames,
        string $moduleDir,
        string $logDir
    ): void {
        foreach ($funcNames as $funcName) {
            echo "Creating container for $funcName\n";

            // the following will run inside primary webserver container with a pwd of /var/www/html
            shell_exec("docker-compose -f $moduleDir/docker-compose-secondary.yml run " .
                "--name myphpunit-$funcName -d --no-deps webserver_service_secondary " .
                "bash -c 'vendor/bin/phpunit --filter=$funcName $testDir > $logDir/$funcName.txt 2>&1'");

            // --no-deps
            // - will use the existing legion_shared_database container, and create a tmp_database within in
            // - (previously when trying to use _a and _b databases, it would just use the _a database)
            // - doesn't show any warnings + best performance for spinning up database containers
            // - fine for silverstripe phpunit as it creates tmp databases
        
            // (omit --no-deps)
            // - Initially will say Creating legion_b_database legion_b_database
            // - The next test will then say 'Starting legion_b_database, though I think using the one
            // currently being used by the first test
            // - Will show anooying message WARNING: Found orphan containers (legion_a_webserver, legion_a_database)
            // for this project.  Also just has slower performance
        }
    }

    protected function waitForTestsToComplete(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $s = shell_exec('docker ps');
            if (preg_match('%myphpunit\-%', $s)) {
                echo "Waiting for tests to complete ...\n";
                sleep(1);
                continue;
            }
            break;
        }
    }

    // TODO: rename $logDir to something more descriptive (it's not actually logs, it's testOutput)
    protected function createLogDir(string $logDir): void
    {
        shell_exec("rm -rf $logDir && mkdir $logDir");
    }

    protected function getTestFunctionNames(string $testDir): array
    {
        // TODO: hardcoded
        // TODO: do the other TODO in this file to use container ID instead of funcName as container ID
        // TODO: change return to path relative to testDir - [$path][$funcName]
        // TODO: unit test
        // should do a recursive file scan and find anything that 'extends <phpunittestcase>|SapphireTest|FunctionalTest'
        // then, for each file, should find 'function test' (using regex below)
        $s = file_get_contents("$testDir/MyTest.php");
        preg_match_all('%function (test[^\( ]*)%', $s, $m);
        return $m[1];
    }

    protected function removeSecondaryContainers(array $funcNames): void
    {
        // remove containers (cannot use --rm with -d in docker-compose run)
        foreach ($funcNames as $funcName) {
            echo "Removing docker container myphpunit-$funcName\n";
            shell_exec("docker rm myphpunit-$funcName");
        }
    }

    protected function parseTestOutputs(string $logDir): void
    {
        $parser = new PhpUnitTestOutputParser();
        foreach (scandir($logDir) as $filename) {
            if (!preg_match('%^test.*?\.txt$%', $filename)) {
                continue;
            }
            $filepath = "$logDir/$filename";
            $parser->parseTestOutputFile($filepath);
        }
        echo $parser->getFormattedOutput();
    }

    protected function echoExecutionTime(int $timeStart): void
    {
        $executionTime = round(microtime(true) - $timeStart, 2);
        echo "\nTotal Execution Time: $executionTime seconds\n\n";
    }
}
