<?php

use PHPUnit_Framework_TestCase;

class TestDirTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        echo $this->something();
    }

    public function testShouldNotShow()
    {
        $this->assertTrue(true);
    }
    
    public function testHasDifferentExtension()
    {
        echo "SMT is the extension used for Smart Ware II";
        $this->assertTrue(true);
    }

    private function something()
    {
        return 'abc';
    }
}
