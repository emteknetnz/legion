<?php

namespace Emteknetnz\Legion\Tests;

use Emteknetnz\Legion\PhpUnitTestResultParser;
use PHPUnit_Framework_TestCase;

class PhpUnitTestResultParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseTestOutput()
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

    public function testParseTestResult()
    {
        // TODO: TDD
        $inputs = [
            "OK (1 test, 1 assertion)",
            "FAILURES!\nTests: 1, Assertions: 1, Failures: 1.",
            "OK, but incomplete, skipped, or risky tests!\nTests: 1, Assertions: 0, Skipped: 1.",
            "OK, but incomplete, skipped, or risky tests!\nTests: 1, Assertions: 0, Incomplete: 1.",
        ];
        $expecteds = [
            ['tests' => 1, 'assertions' => 1, 'failures' => 1, 'skipped' => 0, 'incomplete' => 0],
            ['tests' => 1, 'assertions' => 1, 'failures' => 1, 'skipped' => 1, 'incomplete' => 0],
            ['tests' => 1, 'assertions' => 0, 'failures' => 0, 'skipped' => 1, 'incomplete' => 0],
            ['tests' => 1, 'assertions' => 0, 'failures' => 0, 'skipped' => 0, 'incomplete' => 1],
        ]
    }
}
