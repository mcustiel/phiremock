<?php
namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Response;

class ExpectationBuilder
{
    private $expectation;

    public function __construct(RequestBuilder $requestBuilder)
    {
        $this->expectation = $requestBuilder->build();
    }

    public function then(ResponseBuilder $responseBuilder)
    {
        $responseBuilderValue = $responseBuilder->build();
        return $this->expectation
            ->setNewScenarioState($responseBuilderValue[0])
            ->setResponse($responseBuilderValue[1]);
    }

    public function proxyTo($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \Exception('Invalid proxy url');
        }
        return $this->noResponse()->setProxyTo($url);
    }

    public function noResponse()
    {
        return $this->expectation->setResponse(new Response());
    }
}
