<?php
namespace Mcustiel\Phiremock\Client\Http;

use Psr\Http\Message\RequestInterface;

interface RemoteConnectionInterface
{
    public function send(RequestInterface $request);

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createEmptyRequest();
}
