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
     *      @SRV\Not(@SRV\NotEmpty),
     *      @SRV\Uri
     * })
     */
    private $proxyTo;
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
     * @var int
     * @SRV\OneOf({
     *      @SRV\Type("null"),
     *      @SRV\AllOf({
     *          @SRV\TypeInteger,
     *          @SRV\Minimum(0)
     *      })
     * })
     */
    private $priority = 0;

    /**
     * @return \Mcustiel\Phiremock\Domain\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Request $request
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Mcustiel\Phiremock\Domain\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Response $response
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getScenarioName()
    {
        return $this->scenarioName;
    }

    public function setScenarioName($scenario)
    {
        $this->scenarioName = $scenario;
        return $this;
    }

    /**
     * @return string
     */
    public function getScenarioStateIs()
    {
        return $this->scenarioStateIs;
    }

    public function setScenarioStateIs($scenarioStateIs)
    {
        $this->scenarioStateIs = $scenarioStateIs;
        return $this;
    }

    /**
     * @return string
     */
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
            'scenarioName'     => $this->scenarioName,
            'scenarioStateIs'  => $this->scenarioStateIs,
            'newScenarioState' => $this->newScenarioState,
            'request'          => $this->request,
            'response'         => $this->response,
            'proxyTo'          => $this->proxyTo,
            'priority'         => $this->priority,
        ];
    }

    /**
     * @return number
     */
    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return string
     */
    public function getProxyTo()
    {
        return $this->proxyTo;
    }

    public function setProxyTo($proxyTo)
    {
        $this->proxyTo = $proxyTo;
        return $this;
    }
}
