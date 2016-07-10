<?php
namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Domain\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\PowerRoute\Common\TransactionData;

class HttpResponseStrategy implements ResponseStrategyInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createResponse(Expectation $expectation, TransactionData $transactionData)
    {
        $responseConfig = $expectation->getResponse();
        $httpResponse = $transactionData->getResponse();
        $httpResponse = $this->getResponseWithBody($responseConfig, $httpResponse);
        $httpResponse = $this->getResponseWithStatusCode($responseConfig, $httpResponse);
        $httpResponse = $this->getResponseWithHeaders($responseConfig, $httpResponse);
        $this->processDelay($responseConfig);

        return $httpResponse;
    }

    /**
     * @param $responseConfig
     */
    private function processDelay(Response $responseConfig)
    {
        if ($responseConfig->getDelayMillis()) {
            $this->logger->debug(
                'Delaying the response for ' . $responseConfig->getDelayMillis() . ' milliseconds'
            );
            usleep($responseConfig->getDelayMillis() * 1000);
        }
    }

    private function getResponseWithHeaders(Response $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getHeaders()) {
            foreach ($responseConfig->getHeaders() as $name => $value) {
                $httpResponse = $httpResponse->withHeader($name, $value);
            }
        }
        return $httpResponse;
    }

    private function getResponseWithStatusCode(Response $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getStatusCode()) {
            $httpResponse = $httpResponse->withStatus($responseConfig->getStatusCode());
        }
        return $httpResponse;
    }

    private function getResponseWithBody(Response $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getBody()) {
            $httpResponse = $httpResponse->withBody(new StringStream($responseConfig->getBody()));
        }
        return $httpResponse;
    }
}
