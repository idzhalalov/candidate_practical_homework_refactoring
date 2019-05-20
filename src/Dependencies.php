<?php

namespace Language;


class Dependencies
{
    protected static $instances = [];
    protected static $dependencies = [
        'DATA_SERVICE_PROVIDER' => 'Language\Libraries\SystemApiStrategy',
        'OUTPUT_PROVIDER' => 'Language\Libraries\StdOutStrategy',
        'API_CALL' => 'Language\ApiCall',
    ];

    protected static function hasDependency($key)
    {
        if ( ! isset(self::$dependencies[$key])) {
            return false;
        }

        return true;
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
        if ( ! self::hasDependency($key)) {
            throw new \Exception('Unknown dependency ' . $key);
        }

        if ( ! self::hasInstance($key)) {
            self::$instances[$key] = new self::$dependencies[$key]();
        }

        return self::$instances[$key];
    }

    public static function getClass($key)
    {
        if ( ! self::hasDependency($key)) {
            throw new \Exception('Unknown dependency ' . $key);
        }

        return self::$dependencies[$key];
    }
}