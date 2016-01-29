<?php
namespace Mcustiel\Phiremock\Server\Http\Implementation;

use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use React\EventLoop\Factory as EventLoop;
use React\Socket\Server as ReactSocket;
use React\Http\Server as ReactServer;
use Zend\Diactoros\ServerRequest;
use React\Stream\BufferedSink;
use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;
use Zend\Diactoros\Response as PsrResponse;

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

    public function listen($port = 1337)
    {
        $this->http->on('request', [$this, onRequest]);
        echo "Server running at http://127.0.0.1:1337\n";

        $this->socket->listen($port);
        $this->loop->run();
    }

    private function onRequest(ReactRequest $request, ReactResponse $response)
    {
        BufferedSink::createPromise($request) ->then(
            function ($body) use ($response, $request) {
                $psrRequest = new ServerRequest(
                    array(),
                    array(),
                    getUriFromRequest($request),

                    $request->getQuery(),
                    $body,
                    array(),
                    array()
                );
                //$psrResponse = $powerRoute->start($psrRequest, new \Zend\Diactoros\Response());
                $psrResponse = $this->requestHandler->execute($psrRequest, new PsrResponse());

                $response->writeHead($psrResponse->getStatusCode(), $psrResponse->getHeaders());
                $response->end($psrResponse->getBody()->__toString());
            },
            function ($reason) {
                throw new \RuntimeException($reason);
            },
            function ($update) {
                throw new \RuntimeException($update);
            }
        );
    }
}
