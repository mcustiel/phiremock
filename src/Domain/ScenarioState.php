<?php

namespace Mcustiel\Phiremock\Domain;

use Mcustiel\SimpleRequest\Annotation\Validator as SRV;

class ScenarioState implements \JsonSerializable
{
    /**
     * @var string
     *
     * @SRV\AllOf({
     *     @SRV\Type("string"),
     *     @SRV\NotEmpty
     * })
     */
    private $scenarioName;

    /**
     * @var string
     *
     * @SRV\AllOf({
     *     @SRV\Type("string"),
     *     @SRV\NotEmpty
     * })
     */
    private $scenarioState;

    /**
     * @param string $name
     * @param string $state
     */
    public function __construct($name = '', $state = '')
    {
        $this->scenarioName = $name;
        $this->scenarioState = $state;
    }

    /**
     * @return string
     */
    public function getScenarioName()
    {
        return $this->scenarioName;
    }

    /**
     * @param string $scenario
     *
     * @return \Mcustiel\Phiremock\Domain\ScenarioState
     */
    public function setScenarioName($scenario)
    {
        $this->scenarioName = $scenario;

        return $this;
    }

    /**
     * @return string
     */
    public function getScenarioState()
    {
        return $this->scenarioState;
    }

    /**
     * @param string $scenarioState
     *
     * @return \Mcustiel\Phiremock\Domain\ScenarioState
     */
    public function setScenarioState($scenarioState)
    {
        $this->scenarioState = $scenarioState;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'scenarioName'   => $this->scenarioName,
            'scenarioState'  => $this->scenarioState,
        ];
    }
}
