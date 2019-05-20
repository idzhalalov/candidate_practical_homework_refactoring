<?php

use Language\Config;
use Language\ApiCall;
use PHPUnit_Framework_TestCase as TestCase;

class LanguageBatchBoTest extends TestCase
{
    protected $langBatchBo, $applications, $cachePath;

    public function setUp()
    {
        parent::setUp();
        $this->langBatchBo = new \Language\LanguageBatchBo();
        $this->applications = Config::get('system.translated_applications');
        $this->cachePath = __DIR__ . '/../cache/';
    }

    public function tearDown()
    {
        parent::tearDown();

        // cleanup cached language files
        foreach($this->applications as $application => $languages) {
            foreach ($languages as $language) {
                $fName = $this->cachePath . $application . '/' . $language
                    . '.php';
                if (file_exists($fName)) {
                    unlink($fName);
                }
            }
        }

        // cleanup cached applet language files
        $fName = $this->cachePath . 'flash/' . 'lang_en.xml';
        if (file_exists($fName)) {
            unlink($fName);
        }
    }

    protected function getLanguageFileContent($language) {
        return ApiCall::call(
            'system_api',
            'language_api',
            array(
                'system' => 'LanguageFiles',
                'action' => 'getLanguageFile'
            ),
            array('language' => $language)
        );
    }

    public function testGenerateLangFiles()
    {
        $this->langBatchBo->generateLanguageFiles();

        foreach($this->applications as $application => $languages) {
            foreach($languages as $language) {
                self::assertTrue(file_exists(
                    $this->cachePath . $application . '/' . $language . '.php'
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
                    $this->cachePath . $application . '/' . $language . '.php'
                );

                $languageResponse = $this->getLanguageFileContent($language);
                self::assertTrue($content == $languageResponse['data']);
            }
        }
    }
}