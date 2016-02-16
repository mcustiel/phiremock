<?php
namespace Mcustiel\Phiremock\Server\Model;

interface ScenarioStorage
{
    const INITIAL_SCENARIO = "Scenario.START";

    /**
     * @param string $name
     * @param string $state
     * @return void
     */
    public function setScenarioState($name, $state);

    /**
     * @param string $name
     * @return string
     */
    public function getScenarioState($name);

    /**
     * @return void
     */
    public function clearScenarios();
}
