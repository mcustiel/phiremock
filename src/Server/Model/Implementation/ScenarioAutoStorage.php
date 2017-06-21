<?php

namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Server\Model\ScenarioStorage;

class ScenarioAutoStorage implements ScenarioStorage
{
    /**
     * @var string[]
     */
    private $scenarios;

    public function __construct()
    {
        $this->scenarios = [];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ScenarioStorage::setScenarioState()
     */
    public function setScenarioState($name, $state)
    {
        $this->scenarios[$name] = $state;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ScenarioStorage::getScenarioState()
     */
    public function getScenarioState($name)
    {
        if (!isset($this->scenarios[$name])) {
            $this->scenarios[$name] = self::INITIAL_SCENARIO;
        }

        return $this->scenarios[$name];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ScenarioStorage::clearScenarios()
     */
    public function clearScenarios()
    {
        $this->scenarios = [];
    }
}
