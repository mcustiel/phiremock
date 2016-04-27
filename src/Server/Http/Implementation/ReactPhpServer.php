<?php
namespace Mcustiel\Phiremock\Server\Http\Implementation;

use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use React\EventLoop\Factory as EventLoop;
use React\Socket\Server as ReactSocket;
use React\Http\Server as ReactServer;
use Zend\Diactoros\ServerRequest;
use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;
use Zend\Diactoros\Response as PsrResponse;
use React\Http\Request;
use Psr\Log\LoggerInterface;

class ReactPhpServer implements ServerInterface
{
    /**
     * @var \Mcustiel\Phiremock\Server\Http\RequestHandlerInterface
     */
    private $requestHandler;
    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;
    /**
     * @var \React\Socket\Server
     */
    private $socket;
    /**
     * @var \React\Http\Server
     */
    private $http;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->loop = EventLoop::create();
        $this->socket = new ReactSocket($this->loop);
        $this->http = new ReactServer($this->socket, $this->loop);
        $this->logger = $logger;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Server\Http\ServerInterface::setRequestHandler()
     */
    public function setRequestHandler(RequestHandlerInterface $handler)
    {
        $this->requestHandler = $handler;
    }

    /**
     * @param integer $port
     * @param string  $host
     */
    public function listen($port, $host)
    {
        $this->http->on(
            'request',
            function (ReactRequest $request, ReactResponse $response) {
                return $this->onRequest($request, $response);
            }
        );
        $this->logger->info("Phiremock http server listening on $host:$port");

        $this->socket->listen($port, $host);
        $this->loop->run();
    }

    public function shutdown()
    {
        $this->loop->stop();
    }

    private function getUriFromRequest(Request $request)
    {
        $query = $request->getQuery();
        return 'http://localhost/' . $request->getPath() . (empty($query) ? '' : "?{$query}");
    }

    private function convertFromReactToPsrRequest(Request $request, $body)
    {
        return new ServerRequest(
            [
                'REMOTE_ADDR'  => $request->getRemoteAddress(),
                'HTTP_VERSION' => $request->getHttpVersion()
            ],
            [],
            $this->getUriFromRequest($request),
            $request->getMethod(),
            $body,
            $request->getHeaders(),
            [],
            $request->getQuery()
        );
    }

    private function onRequest(ReactRequest $request, ReactResponse $response)
    {
        $start = microtime(true);

        $psrResponse = $this->requestHandler->execute(
            $this->convertFromReactToPsrRequest(
                $request,
                'data://text/plain;base64,' . base64_encode($request->getBody())
            ),
            new PsrResponse()
        );

        $this->logger->debug(
            'Processing took ' . number_format((microtime(true) - $start) * 1000, 3) . ' milliseconds'
        );
        $response->writeHead($psrResponse->getStatusCode(), $psrResponse->getHeaders());
        $response->end($psrResponse->getBody()->__toString());
    }
}
