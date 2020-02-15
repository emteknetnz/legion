<?php

echo "runtests.php\n";


// TODO: delete contents of $dir

// run tests
// $filepath = "$dir/0001.txt";
// echo "writing to $filepath\n";
// file_put_contents($filepath, 'abc');


// ---


$logdir = dirname(__FILE__) . '/testresults';
$moduledir = 'vendor/emteknetnz/legion';
$unittestdir = 'app/src';

// TODO: hardcoded
$s = file_get_contents(dirname(__FILE__) . "/../../../$unittestdir/MyTest.php");


$timestart = microtime(true); 

shell_exec("rm -rf $logdir && mkdir $logdir");

preg_match_all('%function (test[^\( ]*)%', $s, $m);
foreach ($m[1] as $funcname) {
    echo "Creating container for $funcname\n";
    //shell_exec("docker run --name myphpunit-$funcname --rm -d -v $(pwd):/a php:cli bash -c '/a/vendor/bin/phpunit --filter=$funcname /a/unittests.php > /a/phpunitlogs/$funcname.txt 2>&1'");

    // the following will run inside container A with a pwd of /var/www/html
    shell_exec("docker-compose -f $moduledir/docker-compose-b.yml run --name myphpunit-$funcname --rm -d webserver vendor/bin/phpunit --filter=$funcname $unittestdir > $moduledir/$logdir/$funcname.txt 2>&1'");
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

// TODO: something that better parses all the results in phpunitlogs
foreach (scandir($logdir) as $filename) {
    if (!preg_match('%^test.*?\.txt$%', $filename)) {
        continue;
    }
    echo "\n### $filename\n";
    $s = file_get_contents("$logdir/$filename");
    $s = preg_replace('%PHPUnit .+? by Sebastian Bergmann[^\n]*%', '', $s);
    echo $s;
}

$executiontime = round(microtime(true) - $timestart, 2);
echo "\n\nTotal Execution Time: $executiontime seconds\n\n";