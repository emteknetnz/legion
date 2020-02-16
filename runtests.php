<?php

// This should only be run from within container_a
// This file is called by the host will should run fromhost.php

if (!file_exists('/home/is_legion_docker.txt')) {
    echo "This file should only be run from within docker container_a\n";
    die;
}

if (count($argv) < 2) {
    echo "Usage: php runtests.php [testdir]\n";
    die;
}

$basedir = dirname(__FILE__) . "/../../..";
$testdir = "$basedir/" . $argv[1];
$logdir = dirname(__FILE__) . '/testresults';
$moduledir = dirname(__FILE__);

// TODO: hardcoded
$s = file_get_contents("$testdir/MyTest.php");

$timestart = microtime(true); 

shell_exec("rm -rf $logdir && mkdir $logdir");

preg_match_all('%function (test[^\( ]*)%', $s, $m);
$funcnames = $m[1];
foreach ($funcnames as $funcname) {
    echo "Creating container for $funcname\n";

    // this will spin up a new webserver_b that will be removed after phpunit
    // is run from inside of it

    // --no-deps
    // - will use the existing legion_a_database (instead of legion_b_database)
    // - doesn't show any warnings
    // - best performance for spinning up database containers
    // - shoudl be fine for silverstripe phpunit since it will create tmp databases

    // (omit --no-deps)
    // - Initially will say Creating legion_b_database legion_b_database
    // - The next test will then say 'Starting legion_b_database, though I think using the one currently being used by the first test
    // - Will show anooying message WARNING: Found orphan containers (legion_a_webserver, legion_a_database) for this project
    // - Slower performance

    // the following will run inside container A with a pwd of /var/www/html
    shell_exec("docker-compose -f $moduledir/docker-compose-b.yml run --name myphpunit-$funcname -d --no-deps webserver_service_b bash -c 'vendor/bin/phpunit --filter=$funcname $testdir > $logdir/$funcname.txt 2>&1'");
}

for ($i = 0; $i < 10; $i++) {
    $s = shell_exec('docker ps');
    if (preg_match('%myphpunit\-%', $s)) {
        echo "Waiting for tests to complete ...\n";
        sleep(1);
        continue;
    }
    break;
}

// remove containers (cannot use --rm with -d in docker-compose run)
foreach ($funcnames as $funcname) {
    echo "Removing docker container myphpunit-$funcname\n";
    shell_exec("docker rm myphpunit-$funcname");
}

// TODO: something that better parses all the results in phpunitlogs
foreach (scandir($logdir) as $filename) {
    if (!preg_match('%^test.*?\.txt$%', $filename)) {
        continue;
    }
    echo "\nParsing $filename\n";
    $s = file_get_contents("$logdir/$filename");
    $s = preg_replace('%PHPUnit .+? by Sebastian Bergmann[^\n]*%', '', $s);
    //echo $s;
}

$executiontime = round(microtime(true) - $timestart, 2);
echo "\n\nTotal Execution Time: $executiontime seconds\n\n";

// from within container A:
// php vendor/emteknetnz/legion/runtests.php
