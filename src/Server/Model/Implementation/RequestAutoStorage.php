<?php

namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Psr\Http\Message\ServerRequestInterface;

class RequestAutoStorage implements RequestStorage
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface[]
     */
    private $requests;

    public function __construct()
    {
        $this->clearRequests();
    }

    public function addRequest(ServerRequestInterface $request)
    {
        $this->requests[] = $request;
    }

    public function listRequests()
    {
        return $this->requests;
    }

    public function clearRequests()
    {
        $this->requests = [];
    }
}
