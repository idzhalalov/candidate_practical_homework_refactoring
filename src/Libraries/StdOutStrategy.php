<?php

namespace Language\Libraries;

class StdOutStrategy implements OutputInterface
{
    protected $formatter;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function print($data)
    {
        echo $this->formatter->format($data);
    }
}
