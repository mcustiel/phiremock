<?php
namespace Mcustiel\Phiremock\Server\Model;

use Mcustiel\Phiremock\Server\Domain\Expectation;

interface ExpectatationStorage
{
    public function addExpectation(Expectation $expectation);

    public function clearExpectations();

    public function listExpectations();
}
