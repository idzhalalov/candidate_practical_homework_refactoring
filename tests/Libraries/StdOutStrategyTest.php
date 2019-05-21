<?php

namespace Language\Tests;

use Faker;
use Language\Libraries\StdOutStrategy;
use Language\Libraries\StdOutFormatter;
use PHPUnit_Framework_TestCase as TestCase;

class StdOutStrategyTest extends TestCase
{
    protected $output, $faker, $formatter;

    public function setUp()
    {
        parent::setUp();
        $this->formatter = new StdOutFormatter();
        $this->output = new StdOutStrategy($this->formatter);
        $this->faker = Faker\Factory::create();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSend()
    {
        $str = $this->faker->text;

        ob_start();
        $this->output->send($str);
        $output = ob_get_clean();

        self::assertTrue($output === $str . "\n");
    }

    public function testSendAnd()
    {
        $str = $this->faker->text;

        ob_start();
        $this->output->send("\t\t" . $str);
        $output = ob_get_clean();

        self::assertTrue($output === $this->formatter->format("\t\t" . $str));
    }
}