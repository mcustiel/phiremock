<?php

namespace Mcustiel\Phiremock\Server;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\PowerRoute\PowerRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Phiremock implements RequestHandlerInterface
{
    /**
     * @var \Mcustiel\PowerRoute\PowerRoute
     */
    private $router;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(PowerRoute $router, LoggerInterface $logger)
    {
        $this->router = $router;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Http\RequestHandler::execute()
     */
    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            return $this->router->start($request, $response);
        } catch (\Exception $e) {
            $this->logger->warning('Unexpected exception: ' . $e->getMessage());

            return $response->withStatus(500)
                ->withBody(new StringStream($e->getMessage()));
        }
    }
}
