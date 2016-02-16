<?php
namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;

class ExpectationAutoStorage implements ExpectationStorage
{
    /**
     *
     * @var \Mcustiel\Phiremock\Domain\Expectation[]
     */
    private $config;

    /**
     *
     * @var integer[]
     */
    private $counts;

    public function __construct()
    {
        $this->clearExpectations();
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
        $this->counts[] = 0;
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
        $this->counts = [];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectationStorage::getExpectationUses()
     */
    public function getExpectationUses($position)
    {
        return $this->counts[$position];
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\ExpectationStorage::setExpectationUses()
     */
    public function setExpectationUses($position, $value)
    {
        $this->counts[$position] = $value;
    }
}
