<?php
namespace Mcustiel\Phiremock\Server\Model;

use Mcustiel\Phiremock\Domain\Expectation;

interface ExpectationStorage
{
    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     * @return void
     */
    public function addExpectation(Expectation $expectation);

    /**
     * @return void
     */
    public function clearExpectations();

    /**
     * @return \Mcustiel\Phiremock\Domain\Expectation[]
     */
    public function listExpectations();

    /**
     * @param mixed $position
     */
    public function getExpectationUses($position);

    /**
     * @param mixed   $position
     * @param integer $value
     */
    public function setExpectationUses($position, $value);
}
