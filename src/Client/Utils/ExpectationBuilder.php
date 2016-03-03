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
        $this->expectation->setNewScenarioState($responseBuilderValue[0]);
        $this->expectation->setResponse($responseBuilderValue[1]);
        return $this->expectation;
    }

    public function noResponse()
    {
        $this->expectation->setResponse(new Response());

        return $this->expectation;
    }
}
