<?php

namespace Emteknetnz\Legion\Tests;

use Emteknetnz\Legion\PhpUnitTestOutputParser;
use PHPUnit_Framework_TestCase;

class PhpUnitTestOutputParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseTestResult()
    {
        $parser = new PhpUnitTestOutputParser();
        $testResults = [
            "OK (1 test, 1 assertion)",
            "OK (2 tests, 4 assertions)",
            "FAILURES!\nTests: 1, Assertions: 1, Failures: 1.",
            "OK, but incomplete, skipped, or risky tests!\nTests: 2, Assertions: 3, Skipped: 1.",
            "OK, but incomplete, skipped, or risky tests!\nTests: 1, Assertions: 0, Incomplete: 1.",
            "FAILURES!\nTests: 5, Assertions: 6, Failures: 1, Skipped: 1, Incomplete: 1.",
            "ERRORS!\nTests: 5, Assertions: 53, Errors: 1.",
        ];
        $expecteds = [
            ['tests' => 1, 'assertions' => 1, 'failures' => 0, 'skipped' => 0, 'incomplete' => 0, 'errors' => 0],
            ['tests' => 2, 'assertions' => 4, 'failures' => 0, 'skipped' => 0, 'incomplete' => 0, 'errors' => 0],
            ['tests' => 1, 'assertions' => 1, 'failures' => 1, 'skipped' => 0, 'incomplete' => 0, 'errors' => 0],
            ['tests' => 2, 'assertions' => 3, 'failures' => 0, 'skipped' => 1, 'incomplete' => 0, 'errors' => 0],
            ['tests' => 1, 'assertions' => 0, 'failures' => 0, 'skipped' => 0, 'incomplete' => 1, 'errors' => 0],
            ['tests' => 5, 'assertions' => 6, 'failures' => 1, 'skipped' => 1, 'incomplete' => 1, 'errors' => 0],
            ['tests' => 5, 'assertions' => 53, 'failures' => 0, 'skipped' => 0, 'incomplete' => 0, 'errors' => 1],
        ];
        for ($i = 0; $i < count($testResults); $i++) {
            $testResult = $testResults[$i];
            $expected = $expecteds[$i];
            $actual = $parser->parseTestResult($testResult);
            $this->assertEquals($expected, $actual, "i = $i");
        }
    }

    public function testParseTestOutput()
    {
        $parser = new PhpUnitTestOutputParser();
        $testOutput = file_get_contents(__DIR__ . '/fixtures/testOutputOne.txt');
        $expected = file_get_contents(__DIR__ . '/fixtures/testOutputOneExpected.txt');
        $actual = $parser->parseTestOutput($testOutput);
        $this->assertEquals($expected, $actual);
    }

    public function testParseTestOutputFileAndGetFormattedOutput()
    {
        $parser = new PhpUnitTestOutputParser();
        $parser->parseTestOutputFile(__DIR__ . '/fixtures/testOutputOne.txt');
        $parser->parseTestOutputFile(__DIR__ . '/fixtures/testOutputTwo.txt');
        $parser->parseTestOutputFile(__DIR__ . '/fixtures/testOutputThree.txt');
        $expected = file_get_contents(__DIR__ . '/fixtures/testGetFormattedOutputExpected.txt');
        $actual = $parser->getFormattedOutput();
        file_put_contents(__DIR__ . '/../testresults/expected.txt', $expected);
        file_put_contents(__DIR__ . '/../testresults/actual.txt', $actual);
        $this->assertEquals($expected, $actual);
    }
}
