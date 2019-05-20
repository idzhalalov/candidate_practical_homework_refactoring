<?php

use Language\Config;
use Language\ApiCall;
use PHPUnit_Framework_TestCase as TestCase;

class LanguageBatchBoTest extends TestCase
{
    protected $langBatchBo, $applications, $cachePath, $XMLPath;

    public function setUp()
    {
        parent::setUp();
        $this->langBatchBo = new \Language\LanguageBatchBo();
        $this->applications = Config::get('system.translated_applications');
        $this->cachePath = __DIR__ . '/..' . $this->langBatchBo::CACHE_PATH;
        $this->XMLPath = __DIR__ . '/..' . $this->langBatchBo::XML_FILES_PATH;
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
        foreach ($this->langBatchBo::APPLETS as $appletDirectory => $appletLanguageId) {
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
        $this->langBatchBo->generateLanguageFiles();

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
        $this->langBatchBo->generateLanguageFiles();

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
        $this->langBatchBo->generateAppletLanguageXmlFiles();

        foreach ($this->langBatchBo::APPLETS as $appletDirectory => $appletLanguageId) {
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
        $this->langBatchBo->generateAppletLanguageXmlFiles();

        foreach ($this->langBatchBo::APPLETS as $appletDirectory => $appletLanguageId) {
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