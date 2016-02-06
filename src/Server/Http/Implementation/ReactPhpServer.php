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

class ReactPhpServer implements ServerInterface
{
    private $requestHandler;
    private $loop;
    private $socket;
    private $http;

    public function __construct()
    {
        $this->loop = EventLoop::create();
        $this->socket = new ReactSocket($this->loop);
        $this->http = new ReactServer($this->socket, $this->loop);
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

    public function listen($port, $host)
    {
        $this->http->on('request',
            function (ReactRequest $request, ReactResponse $response) {
                return $this->onRequest($request, $response);
            });
        echo "Server running at http://$host:$port\n";

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
                'REMOTE_ADDR' => $request->getRemoteAddress(),
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
        $psrRequest = $this->convertFromReactToPsrRequest(
            $request,
            'data://text/plain,' . $request->getBody()
        );
        $psrResponse = $this->requestHandler->execute(
            $psrRequest,
            new PsrResponse()
        );
        $response->writeHead($psrResponse->getStatusCode(), $psrResponse->getHeaders());
        $response->end($psrResponse->getBody()->__toString());
    }
}
