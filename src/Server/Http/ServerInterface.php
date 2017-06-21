<?php

namespace Mcustiel\Phiremock\Server\Http;

interface ServerInterface
{
    public function setRequestHandler(RequestHandlerInterface $handler);
}
