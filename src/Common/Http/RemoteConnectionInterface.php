<?php
namespace Mcustiel\Phiremock\Common\Http;

use Psr\Http\Message\RequestInterface;

interface RemoteConnectionInterface
{
    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send(RequestInterface $request);
}
