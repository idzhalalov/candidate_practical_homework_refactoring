<?php

namespace Language\Tests;

use Language\Config;
use Language\ApiCall;
use Language\Libraries\DataSource\SystemApiStrategy;
use PHPUnit_Framework_TestCase as TestCase;

class FakeApiCall extends ApiCall
{
    public static function call($target, $mode, $getParameters, $postParameters)
    {
        return [
            'status' => 'OK',
            'data'   => 'File data',
        ];
    }
}

class ApiCallFalse extends FakeApiCall
{
    public static function call($target, $mode, $getParameters, $postParameters)
    {
        return false;
    }
}

class ApiCallStatus extends ApiCallFalse
{
    public static function call($target, $mode, $getParameters, $postParameters)
    {
        return ['status' => 'not ok'];
    }
}

class ApiCallData extends ApiCallFalse
{
    public static function call($target, $mode, $getParameters, $postParameters)
    {
        return ['status' => 1, 'data' => false];
    }
}

class SystemApiStrategyTest extends TestCase
{
    protected $applications, $apiCallOptions;

    public function setUp()
    {
        parent::setUp();
        $this->applications = Config::get('system.translated_applications');
        $this->apiCallOptions = [
            ['language' => $this->applications[0][0]],
        ];
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGet()
    {
        $result = (new SystemApiStrategy(new FakeApiCall()))->getData(
            'LanguageFiles',
            'getAppletLanguages',
            $this->apiCallOptions
        );
        self::assertTrue(strtolower($result['status']) == 'ok');
    }

    public function testGetErrors()
    {
        $api = new SystemApiStrategy(new ApiCallFalse());

        self::setExpectedException('Exception');
        $result = $api->getData(
            'LanguageFiles',
            'getAppletLanguages',
            $this->apiCallOptions
        );
        self::assertTrue($result === false);
        self::assertTrue($api->errors() !== '');
    }

    public function testGetStatusErrors()
    {
        $api = new SystemApiStrategy(new ApiCallStatus());

        self::setExpectedException('Exception');
        $result = $api->getData(
            'LanguageFiles',
            'getAppletLanguages',
            $this->apiCallOptions
        );
        self::assertTrue($result === false);
        self::assertTrue($api->errors() !== '');
    }

    public function testGetDataErrors()
    {
        $api = new SystemApiStrategy(new ApiCallData());

        self::setExpectedException('Exception');
        $api->getData(
            'LanguageFiles',
            'getAppletLanguages',
            $this->apiCallOptions
        );
        self::assertTrue($api->errors() !== '');
    }
}