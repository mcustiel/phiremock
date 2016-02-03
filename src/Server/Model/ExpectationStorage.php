<?php
namespace Mcustiel\Phiremock\Server\Model;

use Mcustiel\Phiremock\Server\Domain\Expectation;

interface ExpectationStorage
{
    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Expectation $expectation
     * @return void
     */
    public function addExpectation(Expectation $expectation);

    /**
     * @return void
     */
    public function clearExpectations();

    /**
     * @return \Mcustiel\Phiremock\Server\Domain\Expectation[]
     */
    public function listExpectations();

    /**
     * @param string $name
     * @param string $state
     */
    //public function setScenarioState($name, $state);

    /**
     * @param string $name
     */
    //public function getScenarioState($name);
}
