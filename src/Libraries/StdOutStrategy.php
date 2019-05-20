<?php

namespace Language\Libraries;

class StdOutStrategy implements OutputInterface
{
    public function print($data, $indent = 0)
    {
        $indent = ($indent > 0) ? str_repeat('  ', $indent) : '';
        echo $indent . $data . "\n";
    }
}
