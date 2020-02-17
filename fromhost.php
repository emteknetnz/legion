<?php

$dockerInstalled = true;
if (is_null(shell_exec('which docker'))) {
    // TODO: link to instructions
    echo "docker must be installed\n";
    $dockerInstalled = false;
}

if (is_null(shell_exec('which docker-compose'))) {
    // TODO: link to instructions
    echo "docker-compose must be installed\n";
    $dockerInstalled = false;
}

if (!$dockerInstalled) {
    die;
}

// current limitation is only one instance of legion on a host machine at once
$conaexists = !is_null(shell_exec('docker ps --filter "name=webserver_name_a"'));

if (!$conaexists) {
    $moduledir = dirname(__FILE__);

    // up -d is pretty nice here,  it will spin it up the container detached, though
    // it will still wait for it to complete before going to the next line of php
    echo "Spinning up primary legion container, this may take a while\n";
    shell_exec("docker-compose -f $moduledir/docker-compose-a.yml up -d");
    echo "Container created\n";
    
    $conaid = trim(shell_exec('docker ps -q --filter "name=webserver_name_a"'));
    echo "Running dev/build flush=1 in container\n";
    shell_exec("docker exec $conaid bash -c 'vendor/bin/sake dev/build flush=1'"); // << is running command on host
    echo "dev/build flush=1 complete\n";
}

// run tests
if (count($argv) < 2) {
    echo "Please specify a test dir to run tests, e.g. php fromhost.php [testdir]\n";
    die;
}
$testdir = $argv[1];
$conaid = trim(shell_exec('docker ps -q --filter "name=webserver_name_a"'));
shell_exec("docker exec $conaid bash -c 'php vendor/emteknetnz/legion/runtests.php $testdir'");

// php vendor/emteknetnz/legion/fromhost.php 

// docker exec f2b7cdb4577a bash -c "ls /home"

// docker exec f2b7cdb4577a bash -c \'ls /home\'

// docker exec f2b7cdb4577a bash -c "vendor/bin/sake dev/build flush=1"

// docker-compose -f vendor/emteknetnz/legion/docker-compose-a.yml up