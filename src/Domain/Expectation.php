<?php
namespace Mcustiel\Phiremock\Domain;

use Mcustiel\SimpleRequest\Annotation\Validator as SRV;
use Mcustiel\SimpleRequest\Annotation\ParseAs;

class Expectation implements \JsonSerializable
{
    /**
     * @var Request
     *
     * @SRV\NotNull
     * @ParseAs("\Mcustiel\Phiremock\Domain\Request")
     */
    private $request;
    /**
     * @var Response
     *
     * @SRV\NotNull
     * @ParseAs("\Mcustiel\Phiremock\Domain\Response")
     */
    private $response;
    /**
     * @var string
     *
     * @SRV\OneOf({
     *      @SRV\Type("null"),
     *      @SRV\AllOf({
     *          @SRV\Type("string"),
     *          @SRV\NotEmpty
     *      })
     * })
     */
    private $scenarioName;
    /**
     * @var string
     *
     * @SRV\OneOf({
     *      @SRV\Type("null"),
     *      @SRV\AllOf({
     *          @SRV\Type("string"),
     *          @SRV\NotEmpty
     *      })
     * })
     */
    private $scenarioStateIs;
    /**
     * @var string
     *
     * @SRV\OneOf({
     *      @SRV\Type("null"),
     *      @SRV\AllOf({
     *          @SRV\Type("string"),
     *          @SRV\NotEmpty
     *      })
     * })
     */
    private $newScenarioState;

    /**
     * @var integer
     * @SRV\OneOf({
     *      @SRV\Type("null"),
     *      @SRV\AllOf({
     *          @SRV\TypeInteger,
     *          @SRV\Minimum(0)
     *      })
     * })
     */
    private $priority = 0;

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

    public function jsonSerialize()
    {
        return [
            'scenarioName' => $this->scenarioName,
            'scenarioStateIs' => $this->scenarioStateIs,
            'newScenarioState' => $this->newScenarioState,
            'request' => $this->request,
            'response' => $this->response,
        ];
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }
}
