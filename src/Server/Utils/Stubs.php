<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Server\Domain\Expectation;

class Stubs
{
    /**
     * @var \Mcustiel\Phiremock\Server\Domain\Expectation[]
     */
    private $config;

    public function __construct()
    {
        $this->config = [];
    }

    /**
     * @param \Mcustiel\Phiremock\Server\Domain\Expectation $expectation
     */
    public function addStub(Expectation $expectation)
    {
        $this->config[] = $expectation;
    }

    /**
     * @return \Mcustiel\Phiremock\Server\Domain\Expectation[]
     */
    public function getExpectations()
    {
        return $this->config;
    }
}
