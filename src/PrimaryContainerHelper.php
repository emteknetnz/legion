<?php

namespace Emteknetnz\Legion;

class PrimaryContainerHelper
{
    private function isInsideDocker(): bool
    {
        return file_exists('/home/is_legion_docker.txt');
    }

    private function waitForTestsToComplete(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $s = shell_exec('docker ps');
            if (preg_match('%myphpunit\-%', $s)) {
                echo "Waiting for tests to complete ...\n";
                sleep(1);
                continue;
            }
            break;
        }
    }

    public function runTests()
    {
        if (!$this->isInsideDocker()) {
            echo "This file should only be run from within docker container_a\n";
            die;
        }
        
        $argv = $_SERVER['argv'];
        if (count($argv) < 2) {
            echo "Please specify a test directory\n";
            die;
        }
        
        $baseDir = dirname(__FILE__) . "/../../../..";
        $testDir = "$baseDir/" . $argv[1];
        $logDir = dirname(__FILE__) . '/../testresults';
        $moduleDir = dirname(__FILE__) . '/..';
        
        // TODO: hardcoded
        $s = file_get_contents("$testDir/MyTest.php");
        
        $timeStart = microtime(true);
        
        shell_exec("rm -rf $logDir && mkdir $logDir");
        
        preg_match_all('%function (test[^\( ]*)%', $s, $m);
        $funcNames = $m[1];
        foreach ($funcNames as $funcName) {
            echo "Creating container for $funcName\n";
        
            // this will spin up a new webserver_b that will be removed after phpunit
            // is run from inside of it
        
            // --no-deps
            // - will use the existing legion_a_database (instead of legion_b_database)
            // - doesn't show any warnings
            // - best performance for spinning up database containers
            // - shoudl be fine for silverstripe phpunit since it will create tmp databases
        
            // (omit --no-deps)
            // - Initially will say Creating legion_b_database legion_b_database
            // - The next test will then say 'Starting legion_b_database, though I think using the one
            // currently being used by the first test
            // - Will show anooying message WARNING: Found orphan containers (legion_a_webserver, legion_a_database)
            //   for this project
            // - Slower performance
        
            // the following will run inside container A with a pwd of /var/www/html
            shell_exec("docker-compose -f $moduleDir/docker-compose-b.yml run " .
                "--name myphpunit-$funcName -d --no-deps webserver_service_b " .
                "bash -c 'vendor/bin/phpunit --filter=$funcName $testDir > $logDir/$funcName.txt 2>&1'");
        }

        $this->waitForTestsToComplete();
                
        // remove containers (cannot use --rm with -d in docker-compose run)
        foreach ($funcNames as $funcName) {
            echo "Removing docker container myphpunit-$funcName\n";
            shell_exec("docker rm myphpunit-$funcName");
        }
        
        // TODO: something that better parses all the results in phpunitlogs
        foreach (scandir($logDir) as $filename) {
            if (!preg_match('%^test.*?\.txt$%', $filename)) {
                continue;
            }
            echo "Parsing $filename\n";
            $s = file_get_contents("$logDir/$filename");
            $s = preg_replace('%PHPUnit .+? by Sebastian Bergmann[^\n]*%', '', $s);
            echo $s;
        }
        
        $executionTime = round(microtime(true) - $timeStart, 2);
        echo "\nTotal Execution Time: $executionTime seconds\n\n";
    }
}
