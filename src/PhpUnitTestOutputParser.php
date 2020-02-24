<?php

namespace Emteknetnz\Legion;

use Exception;

class PhpUnitTestOutputParser
{
    protected $parsedTestOutputs = [];

    protected $tests = 0;

    protected $assertions = 0;

    protected $failures = 0;

    protected $skipped = 0;

    protected $incomplete = 0;

    public function parseTestOutputFile(string $filepath): void
    {
        $testOutput = file_get_contents($filepath);
        $this->parsedTestOutputs[] = $this->parseTestOutput($testOutput);
    }

    public function parseTestOutput(string $testOutput): string
    {
        // TODO: check that output is using PHPUnit 5.7, otherwise through exeception
        $testOutput = preg_replace('%PHPUnit .+? by Sebastian Bergmann[^\n]*%', '', $testOutput);
        $testOutput = preg_replace("%\nTime:.+?B\n%", '', $testOutput);
        $testOutput = str_replace("\n\n", "\n", $testOutput);
        $rx = '%(?s)((OK|FAILURE).+?[\.\)])%';
        preg_match($rx, $testOutput, $m);
        // this isn't ideal, though keep it for not to help debug this sporadic error
        // TODO: remove if I haven't seen this in a long while
        if (!isset($m[1])) {
            var_dump($testOutput);
            throw new Exception('COULD NOT PARSE TEST OUTPUT');
        }
        $testResult = $m[1];
        $testOutput = preg_replace($rx, '', $testOutput);
        $testNumbers = $this->parseTestResult($testResult);
        $this->addTestResultToTotals($testNumbers);
        return trim($testOutput);
    }

    protected function addTestResultToTotals(array $testNumbers): void
    {
        $this->tests += $testNumbers['tests'];
        $this->assertions += $testNumbers['assertions'];
        $this->failures += $testNumbers['failures'];
        $this->skipped += $testNumbers['skipped'];
        $this->incomplete += $testNumbers['incomplete'];
    }

    /**
     * e.g. OK (1 test, 1 assertion)
     */
    public function parseTestResult(string $testResult): array
    {
        $testNumbers = [
            'tests' => 0,
            'assertions' => 0,
            'failures' => 0,
            'skipped' => 0,
            'incomplete' => 0,
        ];
        $s = $testResult;
        if (preg_match('%^OK \(([0-9]+) tests?, ([0-9]+) assertions?\)$%', $s, $m)) {
            $testNumbers['tests'] += $m[1];
            $testNumbers['assertions'] += $m[2];
        } else {
            $s = str_replace("OK, but incomplete, skipped, or risky tests!\n", '', $s);
            $s = str_replace("FAILURES!\n", '', $s);
            $s = preg_replace('%\.$%', '', $s);
            // Tests: 5, Assertions: 6, Failures: 1, Skipped: 1, Incomplete: 1
            foreach (preg_split('%, %', $s) as $fr) {
                $kv = preg_split('%: %', $fr);
                $k = strtolower($kv[0]);
                $testNumbers[$k] += $kv[1];
            }
        }
        return $testNumbers;
    }

    public function getFormattedOutput(): string
    {
        $output = implode("\n", $this->parsedTestOutputs);
        $output .= "\n" . implode(', ', [
            "Tests: " . $this->tests,
            "Assertions: " . $this->assertions,
            "Failures: " . $this->failures,
            "Skipped: " . $this->skipped,
            "Incomplete: " . $this->incomplete
        ]) . "\n";
        return $output;
    }
}
