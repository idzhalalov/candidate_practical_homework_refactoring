<?php

namespace Language\Libraries\Output;

use Language\Dependencies;

class LoggerStrategy implements OutputInterface
{
    protected $formatter, $logger;

    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        $this->logger = Dependencies::getInstance('LOGGER');
    }

    public function send($data)
    {
        $this->logger->setLevel('info');
        $this->logger->log($this->formatter->format($data), 'info');
        $this->logger->setLevel();
    }
}
