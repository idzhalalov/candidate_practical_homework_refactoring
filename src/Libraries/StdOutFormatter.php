<?php

namespace Language\Libraries;

class StdOutFormatter implements FormatterInterface
{
    public function format($data)
    {
        return $data . "\n";
    }
}
