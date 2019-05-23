<?php

namespace Language\Libraries\Output;

class LoggerFormatter implements FormatterInterface
{
    public function format($data)
    {
        $result = str_replace("\t", '', $data);
        $result = str_replace("\n", '', $result);

        return $result;
    }
}
