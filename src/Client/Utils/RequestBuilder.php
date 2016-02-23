<?php
namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Expectation;

class RequestBuilder
{
    private $request;
    private $headers = [];
    private $scenarioName;
    private $scenarioIs;
    private $priority;

    private function __construct($method)
    {
        $this->request = new Request();
        $this->request->setMethod($method);
    }

    public static function create($method)
    {
        return new static($method);
    }

    public function andBody(Condition $condition)
    {
        $this->request->setBody($condition);
        return $this;
    }

    public function andHeader($header, Condition $condition)
    {
        $this->headers[$header] = $condition;
        return $this;
    }

    public function andUrl(Condition $condition)
    {
        $this->request->setUrl($condition);
        return $this;
    }

    public function andScenarioState($scenario, $scenarioState)
    {
        $this->scenarioName = $scenario;
        $this->scenarioIs = $scenarioState;
        return $this;
    }

    public function andPriority($priority)
    {
        $this->priority = $priority;
    }

    public function build()
    {
        if (!empty($this->headers)) {
            $this->request->setHeaders($this->headers);
        }
        $expectation = new Expectation();
        $expectation->setRequest($this->request);
        if ($this->scenarioName && $this->scenarioIs) {
            $expectation->setScenarioName($this->scenarioName)
                ->setScenarioStateIs($this->scenarioIs);
        }
        if ($this->priority) {
            $expectation->setPriority((integer) $this->priority);
        }
        return $expectation;
    }
}
