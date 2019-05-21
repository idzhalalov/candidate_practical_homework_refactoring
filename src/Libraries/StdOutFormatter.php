<?php

namespace Language\Libraries;

class StdOutFormatter implements FormatterInterface
{
    protected $indentSymbol = '  ';
    public function format($data)
    {
        $result = str_replace("\t", $this->indentSymbol, $data) . "\n";

        return $result;
    }
}
