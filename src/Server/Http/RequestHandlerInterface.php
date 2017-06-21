<?php

namespace Mcustiel\Phiremock\Server\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestHandlerInterface
{
    public function execute(ServerRequestInterface $request, ResponseInterface $response);
}
