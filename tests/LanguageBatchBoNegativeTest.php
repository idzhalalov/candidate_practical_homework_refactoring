<?php

namespace Language\Tests;

use Faker;
use Language\ApiCall;
use Language\LanguageBatchBo;
use Language\Dependencies;
use Language\Libraries\DataSource\DataSourceException as DataSourceException;
use PHPUnit_Framework_TestCase as TestCase;

class LanguageBatchBoNegativeTest extends TestCase
{
    protected $langBatchBo, $applications, $cachePath, $XMLPath, $settings, $faker;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Dependencies::$classMapping['API_CALL'] = 'Language\Tests\Mocks\FakeApiCall';
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Dependencies::$classMapping['API_CALL'] = 'Language\ApiCall';
    }

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
        $this->langBatchBo = new LanguageBatchBo();
        $this->settings = Dependencies::getInstance('SETTINGS');

        $this->applications = $this->settings->APPLICATIONS;
        $this->settings->set('CACHE_PATH', '/tests/cache');
        $this->settings->set('XML_FILES_PATH', '/tests/cache/flash');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('Language\LanguageBatchBo');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateLanguageFiles()
    {
        // Make the case when the last language will not be returned
        for ($i = 0; $i < count($this->applications); $i++) {
            // make a valid API response
            Dependencies::getClass('API_CALL')::setFakeResult([
                'status' => 'OK',
                'data' => ApiCall::GET_APPLET_LANGUAGE_FILE_RESULT
            ]);
        }

        ob_start();
        $this->langBatchBo::generateLanguageFiles();
        $output = ob_get_clean();
        self::assertContains(
            'Error during getting language file',
            $output
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateLanguageFile()
    {
        $language = "";
        $application = "";
        $theMethod = $this->getMethod('generateLanguageFile');

        foreach ($this->applications as $app => $languages)
        {
            $application = $app;
            foreach ($languages as $lang) {
                $language = $lang;
                break;
            }
            break;
        }
        self::assertTrue(!empty($application));
        self::assertTrue(!empty($language));

        try {
            $theMethod->invokeArgs($this->langBatchBo,
                [$application, $language]);
        } catch (DataSourceException $e) {
            self::assertContains(
                'Error during getting language file',
                $e->getMessage()
            );
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateAppletLanguageXmlFilesErrorGettingLanguages()
    {
        Dependencies::getClass('API_CALL')::setFakeResult([
            'status' => 'OK',
            'data' => false
        ]);

        ob_start();
        $this->langBatchBo::generateAppletLanguageXmlFiles();
        $output = ob_get_clean();
        self::assertContains(
            'Error getting available languages for applet',
            $output
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateAppletLanguageXmlFilesNoLanguages()
    {
        Dependencies::getClass('API_CALL')::setFakeResult([
            'status' => 'OK',
            'data' => null
        ]);

        ob_start();
        $this->langBatchBo::generateAppletLanguageXmlFiles();
        $output = ob_get_clean();
        self::assertContains(
            'no available languages for the',
            $output
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateAppletLanguageXmlFilesErrorGettingLanguageFileContent()
    {
        // getting available languages response
        Dependencies::getClass('API_CALL')::setFakeResult([
            'status' => 'OK',
            'data' => ['en']
        ]);

        ob_start();
        $this->langBatchBo::generateAppletLanguageXmlFiles();
        $output = ob_get_clean();
        self::assertContains(
            'Getting language xml for applet',
            $output
        );
        self::assertContains(
            'was unsuccessful',
            $output
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateAppletLanguageXmlFilesErrorSavingFile()
    {
        $this->settings->set('APPLET_FILE_PREFIX', $this->faker->text(
            2000
        ));

        // getting available languages response
        Dependencies::getClass('API_CALL')::setFakeResult([
            'status' => 'OK',
            'data' => ['en']
        ]);
        // getting content response
        Dependencies::getClass('API_CALL')::setFakeResult([
            'status' => 'OK',
            'data' => ApiCall::GET_APPLET_LANGUAGE_FILE_RESULT
        ]);

        ob_start();
        $this->langBatchBo::generateAppletLanguageXmlFiles();
        $output = ob_get_clean();
        self::assertContains('File name too long', $output);
    }
}