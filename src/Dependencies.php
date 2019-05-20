<?php

namespace Language;


class Dependencies
{
    protected static $instances = [];
    protected static $dependencies = [
        'DATA_SERVICE_PROVIDER' => 'Language\Libraries\SystemApiStrategy',
        'OUTPUT_PROVIDER' => 'Language\Libraries\StdOutStrategy'
    ];

    public static function getInstance($key) {
        if (!isset(self::$dependencies[$key])) {
            throw new \Exception('Unknown dependency ' . $key);
        }

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self::$dependencies[$key]();
        }

        return self::$instances[$key];
    }
}