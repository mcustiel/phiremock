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
    public function getScenarioState()
    {
        return $this->scenarioState;
    }

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
