<?php
namespace Mcustiel\Phiremock\Server\Domain;

use Mcustiel\SimpleRequest\Annotation\Validator as SRV;
use Mcustiel\SimpleRequest\Annotation\ParseAs;

class Expectation
{
    /**
     * @var Request
     *
     * @SRV\NotNull
     * @ParseAs("\Mcustiel\Phiremock\Server\Domain\Request")
     */
    private $request;
    /**
     * @var Response
     *
     * @ParseAs("\Mcustiel\Phiremock\Server\Domain\Response")
     */
    private $response;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @SRV\AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $scenarioName;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @SRV\AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $scenarioStateIs;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @SRV\AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $newScenarioState;

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getScenarioName()
    {
        return $this->scenarioName;
    }

    public function setScenarioName($scenario)
    {
        $this->scenarioName = $scenario;
        return $this;
    }

    public function getScenarioStateIs()
    {
        return $this->scenarioStateIs;
    }

    public function setScenarioStateIs($scenarioStateIs)
    {
        $this->scenarioStateIs = $scenarioStateIs;
        return $this;
    }

    public function getNewScenarioState()
    {
        return $this->newScenarioState;
    }

    public function setNewScenarioState($newScenarioState)
    {
        $this->newScenarioState = $newScenarioState;
        return $this;
    }
}
