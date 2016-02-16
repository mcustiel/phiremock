<?php
namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Expectation;

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
        $this->expectation->setNewScenarioState($responseBuilderValue[0]);
        $this->expectation->setResponse($responseBuilderValue[1]);
        return $this->expectation;
    }
}