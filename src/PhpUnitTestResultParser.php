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

    }

    public function getFormattedOutput(): string
    {
        return implode("\n", $this->parsedTestOutputs);
    }
}
