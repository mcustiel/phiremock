<?php

namespace Mcustiel\Phiremock\Server\Model;

use Mcustiel\Phiremock\Domain\Expectation;

interface ExpectationStorage
{
    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     */
    public function addExpectation(Expectation $expectation);

    public function clearExpectations();

    /**
     * @return \Mcustiel\Phiremock\Domain\Expectation[]
     */
    public function listExpectations();
}
