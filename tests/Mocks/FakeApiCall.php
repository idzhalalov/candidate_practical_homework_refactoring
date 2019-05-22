<?php

namespace Language\Tests\Mocks;

use Language\ApiCall;


class FakeApiCall extends ApiCall
{
    public static $result = [];

    public static function call($target, $mode, $getParameters, $postParameters)
    {
        return array_pop(static::$result);
    }

    public static function setFakeResult($data)
    {
        array_push(static::$result, $data);
    }
}