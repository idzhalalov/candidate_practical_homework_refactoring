<?php

namespace Language\Libraries;

class StdOutStrategy implements OutputInterface
{
    protected $formatter;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    public function send($data)
    {
        echo $this->formatter->format($data);
    }
}
