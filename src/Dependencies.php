<?php

namespace Language;


class Dependencies
{
    protected static $instances = [];

    public static $classMapping = [
        'DATA_SERVICE_PROVIDER' => 'Language\Libraries\SystemApiStrategy',
        'OUTPUT_PROVIDER' => 'Language\Libraries\StdOutStrategy',
        'API_CALL' => 'Language\ApiCall',
        'SETTINGS' => 'Language\LanguageBatchBoSettings',
    ];

    protected static function hasDependency($key)
    {
        if ( ! isset(self::$classMapping[$key])) {
            throw new \Exception('Unknown dependency ' . $key);
        }
    }

    protected static function hasInstance($key)
    {
        if ( ! isset(self::$instances[$key])) {
            return false;
        }

        return true;
    }

    public static function getInstance($key)
    {
        self::hasDependency($key);

        if ( ! self::hasInstance($key)) {
            self::$instances[$key] = new self::$classMapping[$key]();
        }

        return self::$instances[$key];
    }

    public static function getClass($key)
    {
        self::hasDependency($key);

        return self::$classMapping[$key];
    }
}