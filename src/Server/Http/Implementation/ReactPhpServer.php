<?php

namespace Mcustiel\Phiremock\Server\Http\Implementation;

use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as EventLoop;
use React\Socket\Server as ReactSocket;
use React\Http\Server as ReactServer;
use React\Http\Response as ReactResponse;
use React\Http\Server as ReactServer;
use React\Socket\Server as ReactSocket;
use Zend\Diactoros\Response as PsrResponse;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Promise;
use Mcustiel\Phiremock\Common\StringStream;

class ReactPhpServer implements ServerInterface
{
    /**
     *
     * @var \Mcustiel\Phiremock\Server\Http\RequestHandlerInterface
     */
    private $requestHandler;

    /**
     *
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     *
     * @var \React\Socket\Server
     */
    private $socket;

    /**
     *
     * @var \React\Http\Server
     */
    private $http;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->loop = EventLoop::create();
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Http\ServerInterface::setRequestHandler()
     */
    public function setRequestHandler(RequestHandlerInterface $handler)
    {
        $this->requestHandler = $handler;
    }

    /**
     *
     * @param int $port
     * @param string $host
     */
    public function listen($port, $host)
    {
        $this->http = new ReactServer(
            function (ServerRequestInterface $request) {
                return $this->createRequestManager($request);
            }
        );

        $this->logger->info("Phiremock http server listening on $host:$port");
        $this->socket = new ReactSocket("$host:$port", $this->loop);
        $this->http->listen($this->socket);

        // Dispatch pending signals periodically
        if (function_exists('pcntl_signal_dispatch')) {
            $this->loop->addPeriodicTimer(0.5, function () {
                pcntl_signal_dispatch();
            });
        }
        $this->loop->run();
    }

    public function shutdown()
    {
        $this->loop->stop();
    }

    private function onRequest(ServerRequestInterface $request)
    {
        $start = microtime(true);
        $psrResponse = $this->requestHandler->execute($request, new PsrResponse());
        $this->logger->debug('Processing took ' . number_format((microtime(true) - $start) * 1000, 3) . ' milliseconds');
        return $psrResponse;
    }

    private function createRequestManager(ServerRequestInterface $request)
    {
        return new Promise(function ($resolve, $reject) use ($request) {
            $body = '';
            $request->getBody()->on('data', function ($data) use (&$body, $request) {
                echo 'on data ======> ' . $data;
                $body .= $data;
            });
            $request->getBody()->on('end', function () use ($resolve, $request, &$body) {
                echo 'on end ======> ' . $body;
                /** @var ServerRequestInterface $request */
                $response = $this->onRequest($request->withBody(new StringStream($body)));
                $resolve($response);
            });
            // an error occures e.g. on invalid chunked encoded data or an unexpected 'end' event
            $request->getBody()->on('error', function (\Exception $exception) use ($resolve) {
                echo 'on error ';
                $response = new ReactResponse(
                    400,
                    array('Content-Type' => 'text/plain'),
                    "An error occured while reading: "
                    );
                $resolve($response);
            });
        });
    }
}
