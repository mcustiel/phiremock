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

    public static function create($statusCode)
    {
        return new static($statusCode);
    }

    public function andBody($body)
    {
        $this->response->setBody($body);
        return $this;
    }

    public function andHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function andDelayInMillis($delay)
    {
        $this->response->setDelayMillis($delay);
        return $this;
    }

    public function andSetScenarioStateTo($scenarioState)
    {
        $this->scenarioState = $scenarioState;
        return $this;
    }

    public function build()
    {
        if (!empty($this->headers)) {
            $this->response->setHeaders($this->headers);
        }
        return [$this->scenarioState, $this->response];
    }
}
