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
     * @ParseAs("\\Mcustiel\\Phiremock\\Server\\Domain\\Request")
     */
    private $request;
    /**
     * @var Response
     *
     * @ParseAs("\\Mcustiel\\Phiremock\\Server\\Domain\\Response")
     */
    private $response;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $setScenario;
    /**
     * @var string
     *
     * @SRV\OneOf({@SRV\Type("null"), @AllOf(@SRV\Type("string"), @SRV\NotEmpty)})
     */
    private $scenarioIs;

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

    public function getSetScenario()
    {
        return $this->setScenario;
    }

    public function setSetScenario($setScenario)
    {
        $this->setScenario = $setScenario;
        return $this;
    }

    public function getScenarioIs()
    {
        return $this->scenarioIs;
    }

    public function setScenarioIs($scenarioIs)
    {
        $this->scenarioIs = $scenarioIs;
        return $this;
    }
}
