<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Psr\Log\LoggerInterface;

trait Loggable
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
