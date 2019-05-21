<?php

namespace Language\Tests;

use Faker;
use Language\Libraries\StdOutStrategy;
use PHPUnit_Framework_TestCase as TestCase;

class StdOutStrategyTest extends TestCase
{
    protected $output, $faker;

    public function setUp()
    {
        parent::setUp();
        $this->output = new StdOutStrategy();
        $this->faker = Faker\Factory::create();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testPrint()
    {
        $str = $this->faker->text;

        ob_start();
        $this->output->print($str);
        $output = ob_get_clean();

        self::assertTrue($output === $str . "\n");
    }

    public function testPrintIndent()
    {
        $str = $this->faker->text;

        ob_start();
        $this->output->print($str, 2);
        $output = ob_get_clean();

        $str = str_repeat('  ', 2) . $str . "\n";
        self::assertTrue($output === $str);
    }
}