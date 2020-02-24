<?php

namespace Emteknetnz\Legion\Tests;

use Emteknetnz\Legion\UnitTestScanner;
use PHPUnit_Framework_TestCase;

class UnitTestScannerTest extends PHPUnit_Framework_TestCase
{
    public function testGetTestFunctionNames()
    {
        $scanner = new UnitTestScanner();
        $s = DIRECTORY_SEPARATOR;
        $testDir = __DIR__ . $s . 'fixtures' . $s .'testdir';
        $expected = [
            'TestDirTest.txt' => [
                'testOne',
                'testTwo'
            ],
            'subdir' . $s . 'SubDirTest.txt' => [
                'testThree',
                'testFour'
            ],
            'subdir' . $s . 'SubDirSecondTest.txt' => [
                'testFive',
                'testSix'
            ],
            'subdir' . $s . 'subsubdir' . $s . 'SubSubDirTest.txt' => [
                'testSeven',
                'testEight'
            ],
        ];
        $actual = $scanner->findUnitTests($testDir, 'txt');
        $this->assertEquals($expected, $actual);
    }
}
