<?php

namespace Emteknetnz\Legion;

class UnitTestScanner
{
    public function findUnitTests(string $testDir, string $extension = 'php'): array
    {
        // TODO: hardcoded
        // TODO: change return to path relative to testDir - [$path][$funcName]
        // TODO: unit test
        // should do a recursive file scan and find anything that
        //  'extends <phpunittestcase>|SapphireTest|FunctionalTest'
        // then, for each file, should find 'function test' (using regex below)
        $s = file_get_contents("$testDir/MyTest.php");
        preg_match_all('%function (test[^\( ]*)%', $s, $m);
        return $m[1];
    }
}
