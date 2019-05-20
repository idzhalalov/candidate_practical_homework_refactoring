<?php

namespace Language;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{
    const CACHE_PATH = '/cache';
    const APPLET_FILE_PREFIX = 'lang_';
    const XML_FILES_PATH = self::CACHE_PATH . '/flash';
    const DATA_SERVICE_PROVIDER = 'Language\SystemApiStrategy';
    const APPLETS = [
        'memberapplet' => 'JSM2_MemberApplet',
    ];

    protected static $dataService;
    protected static $applications = [];

    protected static function dataService()
    {
        if (self::$dataService === null) {
            $className = self::DATA_SERVICE_PROVIDER;
            self::$dataService = new $className();

            if (!self::$dataService instanceof DataSourceStrategy) {
                throw new \Exception(
                    __CLASS__ . '::DATA_SERVICE_PROVIDER must be an instance of ' . __NAMESPACE__ . '\DataSourceStrategy'
                );
            }
        }

        return self::$dataService;
    }

    /**
     * Starts the language file generation.
     *
     * @throws \Exception
     *
     * @return void
     */
    public static function generateLanguageFiles()
    {
        // The applications where we need to translate.
        self::$applications = Config::get('system.translated_applications');

        echo "\nGenerating language files\n";
        foreach (self::$applications as $application => $languages) {
            echo "[APPLICATION: " . $application . "]\n";
            foreach ($languages as $language) {
                echo "\t[LANGUAGE: " . $language . "]";

                try {
                    self::getLanguageFile($application, $language);
                    echo " OK\n";
                } catch (\Exception $e) {
                    throw new \Exception(
                        "Unable to generate language file!\n" .
                        $e->getMessage()
                    );
                }
            }
        }
    }

    /**
     * Gets the language file for the given language and stores it.
     *
     * @param string $application The name of the application.
     * @param string $language    The identifier of the language.
     *
     * @throws \Exception If there was an error during the download of the language file.
     */
    protected static function getLanguageFile($application, $language)
    {
        try {
            $languageResponse = self::dataService()->getData(
                'LanguageFiles',
                'getLanguageFile',
                ['language' => $language]
            );
        } catch (\Exception $e) {
            throw new \Exception(
                "Error during getting language file: (' . $application . '/' . $language . '):\n" .
                $e->getMessage()
            );
        }

        // If we got correct data we store it.
        $destination = Config::get('system.paths.root')
            . self::CACHE_PATH . '/'
            . $application . '/'
            . $language . '.php';

        // If there is no folder yet, we'll create it.
        var_dump($destination);

        try {
            self::storeLanguageFile($languageResponse['data'], $destination);
            echo " OK saving $destination was successful.\n";
        } catch (\Exception $e) {
            throw new \Exception(
                "Unable to save \"$destination\" for language \"$language\": "
                . $e->getMessage());
        }
    }

    /**
     * Gets the language files for the applet and puts them into the cache.
     *
     * @throws \Exception   If there was an error.
     *
     * @return void
     */
    public static function generateAppletLanguageXmlFiles()
    {
        echo "\nGetting applet language XMLs..\n";

        foreach (self::APPLETS as $appletDirectory => $appletLanguageId) {
            echo " Getting > $appletLanguageId ($appletDirectory) language xmls..\n";

            $languages = self::getAppletLanguages($appletLanguageId);
            if (empty($languages)) {
                throw new \Exception('There is no available languages for the '
                    . $appletLanguageId . ' applet.');
            } else {
                echo ' - Available languages: ' . implode(', ', $languages)
                    . "\n";
            }

            $path = Config::get('system.paths.root') . self::XML_FILES_PATH . '/';
            foreach ($languages as $language) {
                $xmlFile = $path . self::APPLET_FILE_PREFIX . $language . '.xml';
                $xmlContent = self::getAppletLanguageFile(
                    $appletLanguageId,
                    $language
                );

                try {
                    self::storeLanguageFile($xmlContent, $xmlFile);
                    echo " OK saving $xmlFile was successful.\n";
                } catch (\Exception $e) {
                    throw new \Exception(
                        "Unable to save \"$xmlFile\" for applet \"$appletLanguageId\": "
                        . $e->getMessage());
                }
            }

            echo " < $appletLanguageId ($appletDirectory) language xml cached.\n";
        }

        echo "\nApplet language XMLs generated.\n";
    }

    /**
     * Gets the available languages for the given applet.
     *
     * @param string $applet The applet identifier.
     *
     * @throws \Exception
     *
     * @return array   The list of the available applet languages.
     */
    protected static function getAppletLanguages($applet)
    {
        try {
            $result = self::dataService()->getData(
                'LanguageFiles',
                'getAppletLanguages',
                ['applet' => $applet]
            );
        } catch (\Exception $e) {
            throw new \Exception('Getting languages for applet (' . $applet
                . ') was unsuccessful ' . $e->getMessage());
        }

        return $result['data'];
    }


    /**
     * Gets a language xml for an applet.
     *
     * @param string $applet   The identifier of the applet.
     * @param string $language The language identifier.
     *
     * @throws \Exception
     *
     * @return string|false   The content of the language file or false if weren't able to get it.
     */
    protected static function getAppletLanguageFile($applet, $language)
    {
        try {
            $result = self::dataService()->getData(
                'LanguageFiles',
                'getAppletLanguageFile',
                [
                    'applet'   => $applet,
                    'language' => $language,
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception(
                "Getting language xml for applet: (' . $applet . ') on language: (' . $language . ') was unsuccessful:\n"
                .
                self::dataService()->errors()
            );
        }

        return $result['data'];
    }

    protected static function storeLanguageFile($content, $destination) {
        if ( ! is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        if (!strlen($content) == file_put_contents($destination, $content)) {
            throw new \Exception('Unable to save file: ' . $destination);
        }
    }
}
