<?php

namespace Emteknetnz\Legion\Tests;

use Emteknetnz\Legion\PhpUnitTestResultParser;
use PHPUnit_Framework_TestCase;

class PhpUnitTestResultParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseTestResult()
    {
        $parser = new PhpUnitTestResultParser();
        $testResults = [
            "OK (1 test, 1 assertion)",
            "OK (2 tests, 4 assertions)",
            "FAILURES!\nTests: 1, Assertions: 1, Failures: 1.",
            "OK, but incomplete, skipped, or risky tests!\nTests: 2, Assertions: 3, Skipped: 1.",
            "OK, but incomplete, skipped, or risky tests!\nTests: 1, Assertions: 0, Incomplete: 1.",
            "FAILURES!\nTests: 5, Assertions: 6, Failures: 1, Skipped: 1, Incomplete: 1."
        ];
        $expecteds = [
            ['tests' => 1, 'assertions' => 1, 'failures' => 1, 'skipped' => 0, 'incomplete' => 0],
            ['tests' => 2, 'assertions' => 4, 'failures' => 1, 'skipped' => 0, 'incomplete' => 0],
            ['tests' => 1, 'assertions' => 1, 'failures' => 1, 'skipped' => 1, 'incomplete' => 0],
            ['tests' => 2, 'assertions' => 3, 'failures' => 0, 'skipped' => 1, 'incomplete' => 0],
            ['tests' => 1, 'assertions' => 0, 'failures' => 0, 'skipped' => 0, 'incomplete' => 1],
            ['tests' => 5, 'assertions' => 6, 'failures' => 1, 'skipped' => 1, 'incomplete' => 1],
        ];
        for ($i = 0; $i < count($testResults); $i++) {
            $testResult = $testResults[$i];
            $expected = $expecteds[$i];
            $actual = $parser->parseTestResult($testResult);
            $this->assertEquals($expected, $actual);
        }
    }

    public function XtestParseTestOutput()
    {
        $parser = new PhpUnitTestResultParser();
        # TODO: use text fixtures
        $input = <<<EOT
PHPUnit 5.7.27 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 2.02 seconds, Memory: 16.00MB

OK (1 test, 1 assertion)
EOT;
        $input = trim($input);
        $expected = <<<EOT
.                                                                   1 / 1 (100%)
EOT;
        $expected = trim($expected);
        $actual = $parser->parseTestOutput($input);
        echo "\n\n";
        var_dump($expected);
        var_dump($actual);
        echo "\n\n";
        $this->assertEquals($expected, $actual);
    }
}
