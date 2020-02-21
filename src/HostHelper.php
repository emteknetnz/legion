<?php

namespace Emteknetnz\Legion;

class HostHelper
{
    public function init()
    {
        $this->checkDockerInstalled();
        $this->createPrimaryContainer();
        $this->runTests();
    }

    protected function checkDockerInstalled(): void
    {
        $dockerInstalled = true;
        if (is_null(shell_exec('which docker'))) {
            echo "docker must be installed - https://docs.docker.com/install/\n";
            $dockerInstalled = false;
        }

        if (is_null(shell_exec('which docker-compose'))) {
            echo "docker-compose must be installed - https://docs.docker.com/compose/install/\n";
            $dockerInstalled = false;
        }

        if (!$dockerInstalled) {
            die;
        }
    }

    protected function createPrimaryContainer(): void
    {
        // current limitation is only one instance of legion on a host machine at once
        $containerAExists = !is_null(shell_exec('docker ps -q --filter "name=webserver_name_a"'));

        if ($containerAExists) {
            echo "Primary legion container already exists, proceeding\n";
        } else {
            $moduleDir = __DIR__ . '/..';

            // up -d is pretty nice here,  it will spin it up the container detached, though
            // it will still wait for it to complete before going to the next line of php
            echo "Spinning up primary legion container, this may take a while\n";
            shell_exec("docker-compose -f $moduleDir/docker-compose-a.yml up -d");
            echo "Container created\n";
            
            $containerAID = trim(shell_exec('docker ps -q --filter "name=webserver_name_a"'));
            echo "Running dev/build flush=1 in container\n";
            shell_exec("docker exec $containerAID bash -c 'vendor/bin/sake dev/build flush=1'");
            echo "dev/build flush=1 complete\n";
        }
    }
    
    protected function runTests(): void
    {
        $argv = $_SERVER['argv'];
        if (count($argv) < 2) {
            echo "Please specify a test-directory i.e. vendor/bin/legion <test-dir>\n";
            die;
        }
        $testDir = $argv[1];
        $containerAID = trim(shell_exec('docker ps -q --filter "name=webserver_name_a"'));
        $command = "php vendor/emteknetnz/legion/primarycontainer.php $testDir";
        echo shell_exec("docker exec $containerAID bash -c '$command'");
    }
}
