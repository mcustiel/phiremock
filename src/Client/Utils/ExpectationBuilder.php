<?php

namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Response;

class ExpectationBuilder
{
    /**
     * @var \Mcustiel\Phiremock\Domain\Expectation
     */
    private $expectation;

    /**
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(RequestBuilder $requestBuilder)
    {
        $this->expectation = $requestBuilder->build();
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Response $responseBuilder
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    public function then(ResponseBuilder $responseBuilder)
    {
        $responseBuilderValue = $responseBuilder->build();

        return $this->expectation
            ->setNewScenarioState($responseBuilderValue[0])
            ->setResponse($responseBuilderValue[1]);
    }

    /**
     * Shortcut.
     *
     * @param int    $statusCode
     * @param string $body
     *
     * return \Mcustiel\Phiremock\Domain\Expectation
     */
    public function thenRespond($statusCode, $body)
    {
        $response = ResponseBuilder::create($statusCode)->andBody($body)->build()[1];

        return $this->expectation->setResponse($response);
    }

    /**
     * @param string $url
     *
     * @throws \Exception
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    public function proxyTo($url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid proxy url');
        }

        return $this->noResponse()->setProxyTo($url);
    }

    /**
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    public function noResponse()
    {
        return $this->expectation->setResponse(new Response());
    }
}
