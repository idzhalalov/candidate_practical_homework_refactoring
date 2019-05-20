<?php

namespace Language;

interface OutputStrategy
{
    public function print($data);
}

class StdOutStrategy implements OutputStrategy
{
    public function print($data, $indent = 0)
    {
        $indent = ($indent > 0) ? str_repeat('  ', $indent) : '';
        echo $indent . $data . "\n";
    }
}
