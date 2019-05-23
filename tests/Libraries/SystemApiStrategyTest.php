<?php

namespace Language\Tests;

use Language\Config;
use Language\Tests\Mocks\FakeApiCall;
use Language\Libraries\DataSource\SystemApiStrategy;
use PHPUnit_Framework_TestCase as TestCase;

class SystemApiStrategyTest extends TestCase
{
    protected $applications, $apiCallOptions, $fakeApiCall;

    public function setUp()
    {
        parent::setUp();
        $this->fakeApiCall = new FakeApiCall();
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
        $this->fakeApiCall->setFakeResult([
            'status' => 'OK',
            'data'   => 'File data',
        ]);

        $result = (new SystemApiStrategy($this->fakeApiCall))->getData(
            'LanguageFiles',
            'getAppletLanguages',
            $this->apiCallOptions
        );
        self::assertTrue(strtolower($result['status']) == 'ok');
    }

    public function testGetErrors()
    {
        $this->fakeApiCall->setFakeResult(false);
        $api = new SystemApiStrategy($this->fakeApiCall);

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
        $this->fakeApiCall->setFakeResult(['status' => 'not ok']);
        $api = new SystemApiStrategy($this->fakeApiCall);

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
        $this->fakeApiCall->setFakeResult(['status' => 1, 'data' => false]);
        $api = new SystemApiStrategy($this->fakeApiCall);

        self::setExpectedException('Exception');
        $api->getData(
            'LanguageFiles',
            'getAppletLanguages',
            $this->apiCallOptions
        );
        self::assertTrue($api->errors() !== '');
    }
}