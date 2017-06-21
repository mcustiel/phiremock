<?php

namespace Mcustiel\Phiremock\Server\Model;

use Psr\Http\Message\ServerRequestInterface;

interface RequestStorage
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function addRequest(ServerRequestInterface $request);

    /**
     * @return \Psr\Http\Message\ServerRequestInterface[]
     */
    public function listRequests();

    public function clearRequests();
}
