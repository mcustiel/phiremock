<?php

namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\PowerRoute\Common\TransactionData;
use Psr\Http\Message\ResponseInterface;

class HttpResponseStrategy extends AbstractResponse implements ResponseStrategyInterface
{
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

    private function getResponseWithBody(Response $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getBody()) {
            $httpResponse = $httpResponse->withBody(new StringStream($responseConfig->getBody()));
        }

        return $httpResponse;
    }
}
