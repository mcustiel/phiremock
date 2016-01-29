<?php
namespace Mcustiel\Phiremock\Server;

use Mcustiel\Phiremock\Server\Model\ExpectatationStorage;
use Mcustiel\Phiremock\Server\Http\RequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mcustiel\PowerRoute\PowerRoute;

class PhiremockServer implements RequestHandler
{
    private $storage;

    private $router;

    public function __construct(
        ExpectatationStorage $storage,
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
