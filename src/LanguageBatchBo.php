<?php

namespace Language;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{

    protected static $applications = [];

    /**
     * Starts the language file generation.
     *
     * @throws \Exception
     *
     * @return void
     */
    public static function generateLanguageFiles()
    {
        self::output()->print("\nGenerating language files:");
        self::$applications = self::settings('APPLICATIONS');

        // The applications where we need to translate
        foreach (self::$applications as $application => $languages) {
            self::output()->print("[APPLICATION: " . $application . "]", 2);

            foreach ($languages as $language) {
                self::output()->print("[LANGUAGE: " . $language . "]", 4);

                try {
                    $fName = self::generateLanguageFile($application, $language);
                    self::output()->print("- $fName ... ok", 6);
                } catch (\Exception $e) {
                    self::output()->print("- $fName ... fail ({$e->getMessage()})", 6);
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
     *
     * @return string Generated file name
     */
    protected static function generateLanguageFile($application, $language)
    {
        try {
            $languageResponse = self::dataService()->getData(
                'LanguageFiles',
                'getLanguageFile',
                ['language' => $language]
            );
        } catch (\Exception $e) {
            throw new \Exception(
                "Error during getting language file: ($application/$language):\n" .
                $e->getMessage()
            );
        }

        // If we got correct data we store it
        $destination = self::settings('ROOT_PATH')
            . self::settings('CACHE_PATH') . '/'
            . $application . '/'
            . $language . '.php';

        try {
            self::storeLanguageFile($languageResponse['data'], $destination);
        } catch (\Exception $e) {
            throw new \Exception(
                "Unable to save \"$destination\" for language \"$language\": " .
                $e->getMessage());
        }

        return $destination;
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
        $path = self::settings('ROOT_PATH') . self::settings('XML_FILES_PATH') . '/';
        self::output()->print("\nGenerating applet language files (XMLs):");

        foreach (self::settings('APPLETS') as $appletDirectory => $appletLanguageId) {
            self::output()->print("[APPLET: $appletLanguageId -> $appletDirectory]", 2);

            $languages = self::getAppletLanguages($appletLanguageId);
            if (empty($languages)) {
                self::output()->print(
                    'There is no available languages for the ' .
                    $appletLanguageId . ' applet.');
                return;
            } else {
                self::output()->print(
                    '[LANGUAGES: ' . implode(', ', $languages) . ']',
                    4
                );
            }

            foreach ($languages as $language) {
                $xmlFile = $path . self::settings('APPLET_FILE_PREFIX') . $language . '.xml';
                $xmlContent = self::getAppletLanguageFile(
                    $appletLanguageId,
                    $language
                );

                try {
                    self::storeLanguageFile($xmlContent, $xmlFile);
                    self::output()->print("- $xmlFile ... ok", 6);
                } catch (\Exception $e) {
                    self::output()->print(
                        "- $xmlFile ... fail (applet \"$appletLanguageId\": {$e->getMessage()})",
                        6
                    );
                }
            }
        }
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

    protected static function dataService()
    {
        return Dependencies::getInstance('DATA_SERVICE_PROVIDER');
    }

    protected static function output()
    {
        return Dependencies::getInstance('OUTPUT_PROVIDER');
    }

    protected static function settings($key)
    {
        return Dependencies::getInstance('SETTINGS')->get($key);
    }
}

