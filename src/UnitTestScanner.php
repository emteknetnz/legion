<?php

namespace Emteknetnz\Legion;

class UnitTestScanner
{
    /**
     * The $extension param is purely to allow using 'txt' files for unit testing
     * see tests/fixtures/testdir
     */
    public function findUnitTests(string $testDir, string $extension = 'php'): array
    {
        $arr = [];
        $this->recursiveScan($testDir, $testDir, $extension, $arr);
        return $arr;
    }

    protected function recursiveScan($currentDir, $testDir, $extension, &$arr)
    {
        foreach (array_diff(scandir($currentDir), ['.', '..']) as $filename) {
            $filepath = $currentDir . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filepath)) {
                $this->recursiveScan($filepath, $testDir, $extension, $arr);
            } else {
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if ($ext !== $extension) {
                    continue;
                }
                $s = file_get_contents($filepath);
                $rx = '% extends (PHPUnit_Framework_TestCase|SapphireTest|FunctionalTest)%';
                if (!preg_match($rx, $s)) {
                    continue;
                }
                preg_match_all('%function (test[^\( ]*)%', $s, $m);
                if (count($m[1]) === 0) {
                    continue;
                }
                $quotedTestDir = preg_quote($testDir);
                $relativeDir = preg_replace("%^$quotedTestDir%", '', $currentDir);
                $relativeDir = ltrim($relativeDir, DIRECTORY_SEPARATOR);
                $key = $relativeDir ? $relativeDir . DIRECTORY_SEPARATOR . $filename : $filename;
                if (!isset($arr[$key])) {
                    $arr[$key] = [];
                }
                foreach ($m[1] as $funcName) {
                    $arr[$key][] = $funcName;
                }
            }
        }
    }
}
