<?php

namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Domain\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class AbstractResponse
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $responseConfig
     */
    protected function processDelay(Response $responseConfig)
    {
        if ($responseConfig->getDelayMillis()) {
            $this->logger->debug(
                'Delaying the response for ' . $responseConfig->getDelayMillis() . ' milliseconds'
            );
            usleep($responseConfig->getDelayMillis() * 1000);
        }
    }

    protected function getResponseWithHeaders(Response $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getHeaders()) {
            foreach ($responseConfig->getHeaders() as $name => $value) {
                $httpResponse = $httpResponse->withHeader($name, $value);
            }
        }

        return $httpResponse;
    }

    protected function getResponseWithStatusCode(Response $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getStatusCode()) {
            $httpResponse = $httpResponse->withStatus($responseConfig->getStatusCode());
        }

        return $httpResponse;
    }
}
