<?php

namespace Language\Tests\Mocks;

use Language\ApiCall;


class FakeApiCall extends ApiCall
{
    public static $result;

    public static function call($target, $mode, $getParameters, $postParameters)
    {
        return static::$result;
    }

    public function setFakeResult($data)
    {
        static::$result = $data;
    }
}