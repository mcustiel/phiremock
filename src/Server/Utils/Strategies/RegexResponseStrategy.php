<?php

namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\PowerRoute\Common\TransactionData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegexResponseStrategy extends AbstractResponse implements ResponseStrategyInterface
{
    public function createResponse(Expectation $expectation, TransactionData $transactionData)
    {
        $responseConfig = $expectation->getResponse();
        $httpResponse = $transactionData->getResponse();
        $httpResponse = $this->getResponseWithBody(
            $expectation,
            $httpResponse,
            $transactionData->getRequest()
        );
        $httpResponse = $this->getResponseWithStatusCode($responseConfig, $httpResponse);
        $httpResponse = $this->getResponseWithHeaders($responseConfig, $httpResponse);
        $this->processDelay($responseConfig);

        return $httpResponse;
    }

    private function getResponseWithBody(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $httpRequest
    ) {
        $responseBody = $expectation->getResponse()->getBody();

        if ($responseBody) {
            $responseBody = $this->fillWithUrlMatches($expectation, $httpRequest, $responseBody);
            $responseBody = $this->fillWithBodyMatches($expectation, $httpRequest, $responseBody);
            $httpResponse = $httpResponse->withBody(new StringStream($responseBody));
        }

        return $httpResponse;
    }

    private function fillWithBodyMatches($expectation, $httpRequest, $responseBody)
    {
        if ($this->bodyConditionIsRegex($expectation)) {
            $responseBody = preg_replace('/\$\{body\.(\d+)\}/', '\$$1', $responseBody);

            return preg_replace(
                $expectation->getRequest()->getBody()->getValue(),
                $responseBody,
                $httpRequest->getBody()->__toString()
            );
        }

        return $responseBody;
    }

    private function bodyConditionIsRegex($expectation)
    {
        return $expectation->getRequest()->getBody()
            && $expectation->getRequest()->getBody()->getMatcher() === Matchers::MATCHES;
    }

    private function fillWithUrlMatches($expectation, $httpRequest, $responseBody)
    {
        if ($this->urlConditionIsRegex($expectation)) {
            $responseBody = preg_replace('/\$\{url\.(\d+)\}/', '\$$1', $responseBody);

            return preg_replace(
                $expectation->getRequest()->getUrl()->getValue(),
                $responseBody,
                $httpRequest->getUri()->__toString()
            );
        }

        return $responseBody;
    }

    private function urlConditionIsRegex($expectation)
    {
        return $expectation->getRequest()->getUrl() && $expectation->getRequest()->getUrl()->getMatcher() === Matchers::MATCHES;
    }
}
