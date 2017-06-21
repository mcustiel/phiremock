<?php

namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;

class ExpectationAutoStorage implements ExpectationStorage
{
    /**
     * @var \Mcustiel\Phiremock\Domain\Expectation[]
     */
    private $expectations;

    public function __construct()
    {
        $this->clearExpectations();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectatationStorage::addExpectation()
     */
    public function addExpectation(Expectation $expectation)
    {
        $this->expectations[] = $expectation;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectatationStorage::listExpectations()
     */
    public function listExpectations()
    {
        return $this->expectations;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectatationStorage::clearExpectations()
     */
    public function clearExpectations()
    {
        $this->expectations = [];
    }
}
