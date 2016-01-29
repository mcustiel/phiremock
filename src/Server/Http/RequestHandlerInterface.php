<?php
namespace Mcustiel\Phiremock\Server\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RequestHandlerInterface
{
    public function execute(ServerRequestInterface $request, ResponseInterface $response);
}
