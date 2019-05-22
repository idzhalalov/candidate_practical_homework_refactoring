<?php

namespace Language;


class Dependencies
{
    protected static $instances = [];

    public static $classMapping = [
        'DATA_SERVICE_PROVIDER' => 'Language\Libraries\DataSource\SystemApiStrategy',
        'API_CALL' => 'Language\ApiCall',
        'SETTINGS' => 'Language\LanguageBatchBoSettings',
        'LOGGER' => [
            __DIR__ . '/../logs.log' => 'Language\Libraries\Logger'
        ],

        # STDOUT strategy
        'OUTPUT_PROVIDER' => [
            'Language\Libraries\Output\StdOutFormatter' => '\Language\Libraries\Output\StdOutStrategy'
        ],

        # Logger strategy
//        'OUTPUT_PROVIDER' => [
//            'Language\Libraries\Output\LoggerFormatter' => '\Language\Libraries\Output\LoggerStrategy'
//        ],
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

            if (is_array(self::$classMapping[$key])) {
                foreach (self::$classMapping[$key] as $k => $v) {
                    $k = (class_exists($k)) ? new $k() : $k;
                    self::$instances[$key] = new $v($k);
                }
            } else {
                self::$instances[$key] = new self::$classMapping[$key]();
            }
        }

        return self::$instances[$key];
    }

    public static function getClass($key)
    {
        self::hasDependency($key);

        return self::$classMapping[$key];
    }
}