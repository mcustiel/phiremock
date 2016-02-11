<?php
namespace Mcustiel\Phiremock\Client;

use Mcustiel\Phiremock\Domain\Expectation;
use Psr\Http\Message\RequestInterface;

class Phiremock
{
    private $connectionFacade;

    public function __construct($remoteConnection)
    {
        $this->connectionFacade = $remoteConnection;
    }

    public function createExpectation(Expectation $expectation)
    {
        $json = json_encode($expectation);
        /**
         * @var \Psr\Http\Message\RequestInterface $request
         */
        $request = $this->connectionFacade->createRequest();

    }

    public function clearExpectations()
    {

    }

    public function listExpectations()
    {

    }

    public function resetScenarios()
    {

    }
}
