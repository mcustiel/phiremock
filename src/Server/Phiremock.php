<?php
namespace Mcustiel\Phiremock\Server;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;

class Phiremock implements RequestHandlerInterface
{
    private $storage;

    private $router;

    public function __construct(
        ExpectationStorage $storage,
        PowerRoute $router
    ) {
        $this->storage = $storage;
        $this->router = $router;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Http\RequestHandler::execute()
     */
    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->router->start($request, $response);
    }
}
