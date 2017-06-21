<?php

namespace Mcustiel\Phiremock\Server\Model;

interface ScenarioStorage
{
    const INITIAL_SCENARIO = 'Scenario.START';

    /**
     * @param string $name
     * @param string $state
     */
    public function setScenarioState($name, $state);

    /**
     * @param string $name
     *
     * @return string
     */
    public function getScenarioState($name);

    public function clearScenarios();
}
