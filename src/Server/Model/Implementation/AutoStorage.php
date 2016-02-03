<?php
namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\Phiremock\Server\StorageInterface;

class AutoStorage implements StorageInterface
{
    const INITIAL_SCENARIO = "Scenario.START";

    /**
     *
     * @var \Mcustiel\Phiremock\Server\Domain\Expectation[]
     */
    private $config;

    /**
     * @var string[]
     */
    private $scenarios;

    public function __construct()
    {
        $this->config = [];
        $this->scenarios = [];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectatationStorage::addExpectation()
     */
    public function addExpectation(Expectation $expectation)
    {
        $this->config[] = $expectation;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectatationStorage::listExpectations()
     */
    public function listExpectations()
    {
        return $this->config;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectatationStorage::clearExpectations()
     */
    public function clearExpectations()
    {
        $this->config = [];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ScenarioStorage::setScenarioState()
     */
    public function setScenarioState($name, $state)
    {
        $this->scenarios[$name] = $state;
    }

    /**
     *
     * {@inheritDoc}
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
     * {@inheritDoc}
     * @see \Mcustiel\Phiremock\Server\Model\ScenarioStorage::clearScenarios()
     */
    public function clearScenarios()
    {
        $this->scenarios = [];
    }
}
