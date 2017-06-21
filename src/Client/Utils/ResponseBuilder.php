<?php

namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Response;

class ResponseBuilder
{
    private $response;

    private $headers = [];

    private $scenarioState;

    private function __construct($statusCode)
    {
        $this->response = new Response();
        $this->response->setStatusCode($statusCode);
    }

    /**
     * @param int $statusCode
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ResponseBuilder
     */
    public static function create($statusCode)
    {
        return new static($statusCode);
    }

    /**
     * @param string $body
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ResponseBuilder
     */
    public function andBody($body)
    {
        $this->response->setBody($body);

        return $this;
    }

    /**
     * @param string $header
     * @param string $value
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ResponseBuilder
     */
    public function andHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * @param int $delay
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ResponseBuilder
     */
    public function andDelayInMillis($delay)
    {
        $this->response->setDelayMillis($delay);

        return $this;
    }

    /**
     * @param string $scenarioState
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ResponseBuilder
     */
    public function andSetScenarioStateTo($scenarioState)
    {
        $this->scenarioState = $scenarioState;

        return $this;
    }

    /**
     * @return string[]|\Mcustiel\Phiremock\Domain\Response[]
     */
    public function build()
    {
        if (!empty($this->headers)) {
            $this->response->setHeaders($this->headers);
        }

        return [$this->scenarioState, $this->response];
    }
}
