<?php

echo "runtests.php\n";

// ---

$basedir = dirname(__FILE__) . "/../../..";
$logdir = dirname(__FILE__) . '/testresults';
$moduledir = dirname(__FILE__);

// TODO: hardcoded
$unittestdir = "$basedir/app/src";

// TODO: hardcoded
$s = file_get_contents("$unittestdir/MyTest.php");

$timestart = microtime(true); 

shell_exec("rm -rf $logdir && mkdir $logdir");

preg_match_all('%function (test[^\( ]*)%', $s, $m);
$funcnames = $m[1];
foreach ($funcnames as $funcname) {
    echo "Creating container for $funcname\n";
    //shell_exec("docker run --name myphpunit-$funcname --rm -d -v $(pwd):/a php:cli bash -c '/a/vendor/bin/phpunit --filter=$funcname /a/unittests.php > /a/phpunitlogs/$funcname.txt 2>&1'");

    // the following will run inside container A with a pwd of /var/www/html
    $cmd = "docker-compose -f $moduledir/docker-compose-b.yml run --name myphpunit-$funcname -d webserver vendor/bin/phpunit --filter=$funcname $unittestdir > $logdir/$funcname.txt 2>&1";
    echo "$cmd\n";
    shell_exec($cmd);
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
    echo $s;
}

$executiontime = round(microtime(true) - $timestart, 2);
echo "\n\nTotal Execution Time: $executiontime seconds\n\n";

// from within container A:
// php vendor/emteknetnz/legion/runtests.php
