<?php

namespace Emteknetnz\Legion;

use Emteknetnz\Legion\UnitTestScanner;

class PrimaryContainerHelper
{
    public function init(): void
    {
        $timeStart = microtime(true);
        $this->checkIsInsideDocker();

        $specifiedTestDir = $this->getSpecifiedTestDir();
        $s = DIRECTORY_SEPARATOR;
        $up = $s . '..';
        $baseDir = __DIR__ . $up . $up . $up . $up;
        $testDir = "$baseDir/" . $specifiedTestDir;
        $testOutputDir = __DIR__ . $up . $s . 'testresults';
        $moduleDir = __DIR__ . $up;
        $scanner = new UnitTestScanner();
        $funcNames = $scanner->findUnitTests($testDir);

        $this->createTestOutputDir($testOutputDir);
        $args = [$testDir, $funcNames, $moduleDir, $testOutputDir];
        $secondaryContainerNames = $this->runTestsInsideSecondaryContainers(...$args);
        $this->waitForTestsToComplete($secondaryContainerNames);
        $this->removeSecondaryContainers($secondaryContainerNames);
        $this->parseTestOutputs($testOutputDir);
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
        string $testOutputDir
    ): array {
        $secondaryContainerNames = [];
        foreach ($funcNames as $funcName) {
            echo "Creating container for $funcName\n";

            // the following will run inside primary webserver container with a pwd of /var/www/html
            $command = "docker-compose -f $moduleDir/docker-compose-secondary.yml run" .
            " --name myphpunit-$funcName -d --no-deps webserver_service_secondary" .
            " bash -c 'vendor/bin/phpunit --filter=$funcName $testDir > $testOutputDir/$funcName.txt 2>&1'";
            $secondaryContainerNames[] = shell_exec($command);

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
        return $secondaryContainerNames;
    }

    protected function waitForTestsToComplete(array $secondaryContainerNames): void
    {
        for ($i = 0; $i < 30; $i++) {
            $s = shell_exec('docker ps');
            foreach ($secondaryContainerNames as $secondaryContainerName) {
                $secondaryContainerName = str_replace('-', '\-', $secondaryContainerName);
                if (preg_match("%$secondaryContainerName%", $s)) {
                    echo "Waiting for tests to complete ...\n";
                    sleep(1);
                    continue;
                }
            }
            break;
        }
    }

    protected function createTestOutputDir(string $testOutputDir): void
    {
        shell_exec("rm -rf $testOutputDir && mkdir $testOutputDir");
    }

    protected function removeSecondaryContainers(array $secondaryContainerNames): void
    {
        // remove containers (cannot use --rm with -d in docker-compose run)
        foreach ($secondaryContainerNames as $secondaryContainerName) {
            echo "Removing docker container $secondaryContainerName\n";
            shell_exec("docker rm $secondaryContainerName");
        }
    }

    protected function parseTestOutputs(string $testOutputDir): void
    {
        $parser = new PhpUnitTestOutputParser();
        foreach (scandir($testOutputDir) as $filename) {
            if (!preg_match('%^test.*?\.txt$%', $filename)) {
                continue;
            }
            $filepath = "$testOutputDir/$filename";
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
