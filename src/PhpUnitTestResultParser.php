<?php

namespace Emteknetnz\Legion;

class PhpUnitTestResultParser
{
    protected $parsedTestOutputs = [];

    protected $tests = 0;

    protected $assertions = 0;

    protected $failures = 0;

    protected $skipped = 0;

    protected $incomplete = 0;

    public function parseTestOutputFile(string $filepath): void
    {
        echo "Parsing $filepath\n";
        $testOutput = file_get_contents($filepath);
        $this->parsedTestOutputs[] = $this->parseTestOutput($testOutput);
    }

    public function parseTestOutput(string $testOutput): string
    {
        // TODO: check that output is using PHPUnit 5.7, otherwise through exeception
        $testOutput = preg_replace('%PHPUnit .+? by Sebastian Bergmann[^\n]*%', '', $testOutput);
        $testOutput = preg_replace("%\nTime:.+?B\n%", '', $testOutput);
        $testOutput = str_replace("\n\n", "\n", $testOutput);

        return trim($testOutput);
    }

    public function parseTestResult(string $testResult): array
    {
        $result = [
            'tests' => 0,
            'assertions' => 0,
            'failures' => 0,
            'skipped' => 0,
            'incomplete' => 0,
        ];
        $s = $testResult;
        if (preg_match('%^OK \(([0-9]+) tests?, ([0-9]+) assertions?\)$%', $s, $m)) {
            $result['tests'] += $m[1];
            $result['assertions'] += $m[2];
        } else {
            $s = str_replace("OK, but incomplete, skipped, or risky tests!\n", '', $s);
            $s = str_replace("FAILURES!\n", '', $s);
            $s = preg_replace('%\.$%', '', $s);
            // Tests: 5, Assertions: 6, Failures: 1, Skipped: 1, Incomplete: 1
            foreach (preg_split('%, %', $s) as $fr) {
                $kv = preg_split('%: %', $fr);
                $k = strtolower($kv[0]);
                $result[$k] += $kv[1];
            }
        }
        return $result;
    }

    public function getFormattedOutput(): string
    {
        return implode("\n", $this->parsedTestOutputs);
    }
}
