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
        if (preg_match('%^OK \(([0-9]+) tests?, ([0-9]+) assertions?\)$%', $testResult, $m)) {
            $result[] += $m[1];
            $assertions += $m[2];
        } elseif (preg_match('%%', $testResult)) {

        } elseif (preg_match('%%', $testResult)) {
            
        }
        return $result;
    }

    public function getFormattedOutput(): string
    {
        return implode("\n", $this->parsedTestOutputs);
    }
}
