<?php

namespace Language\Tests;

use Language\ApiCall;
use Language\LanguageBatchBo;
use Language\Dependencies;
use PHPUnit_Framework_TestCase as TestCase;

class LanguageBatchBoTest extends TestCase
{
    protected $langBatchBo, $applications, $cachePath, $XMLPath, $settings;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        $this->langBatchBo = LanguageBatchBo::class;
        $this->settings = Dependencies::getInstance('SETTINGS');
        $this->settings->set('CACHE_PATH', '/tests/cache');

        $this->applications = $this->settings->APPLICATIONS;
        $this->cachePath = $this->settings->ROOT_PATH . $this->settings->CACHE_PATH;
        $this->XMLPath = $this->settings->ROOT_PATH . $this->settings->XML_FILES_PATH;
    }

    public function tearDown()
    {
        parent::tearDown();

        // cleanup cached language files
        foreach($this->applications as $application => $languages) {
            foreach ($languages as $language) {
                $fName = $this->cachePath . '/'
                    . $application . '/'
                    . $language . '.php';
                if (file_exists($fName)) {
                    unlink($fName);
                }
            }
        }

        // cleanup cached applet language files
        foreach ($this->settings->APPLETS as $appletDirectory => $appletLanguageId) {
            $appletLanguages = $this->getDataViaAPI(
                'getAppletLanguages',
                ['applet' => $appletLanguageId]
            );

            foreach ($appletLanguages['data'] as $language) {
                $fName = $this->XMLPath . '/lang_' . $language . '.xml';
                if (file_exists($fName)) {
                    unlink($fName);
                }
            }
        }
    }

    protected function getDataViaAPI($action, array $params = []) {
        return ApiCall::call(
            'system_api',
            'language_api',
            array(
                'system' => 'LanguageFiles',
                'action' => $action
            ),
            $params
        );
    }

    public function testGenerateLangFiles()
    {
        $this->langBatchBo::generateLanguageFiles();

        foreach($this->applications as $application => $languages) {
            foreach($languages as $language) {
                self::assertTrue(file_exists(
                    $this->cachePath . '/'
                    . $application . '/'
                    . $language . '.php'
                ));
            }
        }
    }

    public function testLangFilesContent()
    {
        $this->langBatchBo::generateLanguageFiles();

        foreach($this->applications as $application => $languages) {
            foreach($languages as $language) {
                $content = file_get_contents(
                    $this->cachePath . '/'
                    . $application . '/'
                    . $language . '.php'
                );
                $resp = $this->getDataViaAPI(
                    'getLanguageFile',
                    ['language' => $language]
                );

                self::assertTrue(
                    $content == $resp['data'],
                    'Expected: ' . $resp['data']
                );
            }
        }
    }

    public function testGenerateAppletLangFiles()
    {
        $this->langBatchBo::generateAppletLanguageXmlFiles();

        foreach ($this->settings->APPLETS as $appletDirectory => $appletLanguageId) {
            $appletLanguages = $this->getDataViaAPI(
                'getAppletLanguages',
                ['applet' => $appletLanguageId]
            );

            foreach ($appletLanguages['data'] as $language) {
                $fName = $this->XMLPath . '/lang_' . $language . '.xml';
                self::assertTrue(file_exists($fName), 'File: ' . $fName);
            }
        }
    }

    public function testAppletLangFilesContent()
    {
        $this->langBatchBo::generateAppletLanguageXmlFiles();

        foreach ($this->settings->APPLETS as $appletDirectory => $appletLanguageId) {
            $appletLanguages = $this->getDataViaAPI(
                'getAppletLanguages',
                ['applet' => $appletLanguageId]
            );

            foreach ($appletLanguages['data'] as $language) {
                $fName = $this->XMLPath . '/lang_' . $language . '.xml';
                $content =  file_get_contents($fName);
                $resp = $this->getDataViaAPI(
                    'getAppletLanguageFile',
                    [
                        'applet' => $appletLanguageId,
                        'language' => $language
                    ]
                );

                self::assertTrue($content === $resp['data']);
            }
        }
    }
}