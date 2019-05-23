<?php

namespace Language;

use Language\Libraries\DataSource\DataSourceException;

/**
 * Business logic related to generating language files.
 */
class LanguageBatchBo
{
    /**
     * Starts the language file generation.
     *
     * @return void
     */
    public static function generateLanguageFiles()
    {
        self::output()->send("\nGenerating language files:");

        // Applications
        foreach (self::settings('APPLICATIONS') as $application => $languages) {
            self::output()->send("\t[APPLICATION: " . $application . "]");

            // Languages
            foreach ($languages as $language) {
                self::output()->send("\t\t[LANGUAGE: " . $language . "]");

                try {
                    $fName = self::generateLanguageFile($application, $language);
                    self::output()->send("\t\t\t- $fName ... ok");
                } catch (\Exception $e) {
                    self::output()->send("\t\t\t- {$e->getMessage()}");
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
     * @throws DataSourceException If there was an error during the download of the language file
     * @throws \Exception If there was an error during saving the language file
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
        } catch (DataSourceException $e) {
            throw new DataSourceException(
                "Error during getting language file: ($application/$language):\n" .
                $e->getMessage()
            );
        }

        $destination = self::settings('ROOT_PATH')
            . self::settings('CACHE_PATH') . '/'
            . $application . '/'
            . $language . '.php';

        // Save a language file
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
     * @return void
     */
    public static function generateAppletLanguageXmlFiles()
    {
        $path = self::settings('ROOT_PATH') . self::settings('XML_FILES_PATH') . '/';
        self::output()->send("\nGenerating applet language files (XMLs):");

        // Applets
        foreach (self::settings('APPLETS') as $appletDirectory => $appletLanguageId) {
            self::output()->send("\t[APPLET: $appletDirectory -> $appletLanguageId]");

            // Languages
            try {
                $languages = self::getAppletLanguages($appletLanguageId);
            } catch (DataSourceException $e) {
                self::output()->send(
                    "Error getting available languages for applet $appletLanguageId: " .
                    $e->getMessage()
                );
                continue;
            }

            if (empty($languages)) {
                self::output()->send(
                    "\t\tThere is no available languages for the $appletLanguageId applet"
                );
                continue;
            } else {
                self::output()->send(
                    "\t\t" . '[LANGUAGES: ' . implode(', ', $languages) . ']'
                );
            }

            foreach ($languages as $language) {
                $xmlFile = $path .
                    self::settings('APPLET_FILE_PREFIX') .
                    $language . '.xml';

                try {
                    $xmlContent = self::getAppletLanguageFileContent(
                        $appletLanguageId,
                        $language
                    );
                } catch (DataSourceException $e) {
                    self::output()->send("\t\t" . $e->getMessage());
                    continue;
                }

                // Save an applet language file
                try {
                    self::storeLanguageFile($xmlContent, $xmlFile);
                    self::output()->send("\t\t\t- $xmlFile ... ok");
                } catch (\Exception $e) {
                    self::output()->send(
                        "\t\t\t- $xmlFile ... fail (applet \"$appletLanguageId\"): {$e->getMessage()})"
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
     * @throws DataSourceException
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
        } catch (DataSourceException $e) {
            throw new DataSourceException($e);
        }

        return $result['data'];
    }


    /**
     * Gets a language xml for an applet.
     *
     * @param string $applet   The identifier of the applet.
     * @param string $language The language identifier.
     *
     * @throws DataSourceException
     *
     * @return string|false   The content of the language file or false if weren't able to get it.
     */
    protected static function getAppletLanguageFileContent($applet, $language)
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
        } catch (DataSourceException $e) {
            throw new DataSourceException(
                'Getting language xml for applet ' .
                "($applet) on language ($language) was unsuccessful: " .
                $e->getMessage()
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

