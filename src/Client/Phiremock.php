<?php

namespace Mcustiel\Phiremock\Client;

use Mcustiel\Phiremock\Client\Utils\ExpectationBuilder;
use Mcustiel\Phiremock\Client\Utils\RequestBuilder;
use Mcustiel\Phiremock\Common\Http\Implementation\GuzzleConnection;
use Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Common\Utils\RequestBuilderFactory;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Response;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request as PsrRequest;
use Zend\Diactoros\Uri;

class Phiremock
{
    const API_EXPECTATIONS_URL = '/__phiremock/expectations';
    const API_EXECUTIONS_URL = '/__phiremock/executions';
    const API_SCENARIOS_URL = '/__phiremock/scenarios';

    /**
     * @var \Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface
     */
    private $connection;
    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $simpleRequestBuilder;
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;

    public function __construct(
        $host = 'localhost',
        $port = 8080,
        RemoteConnectionInterface $remoteConnection = null
    ) {
        if (!$remoteConnection) {
            $remoteConnection = new GuzzleConnection();
        }
        $this->host = $host;
        $this->port = $port;
        $this->connection = $remoteConnection;
    }

    /**
     * Creates an expectation with a response for a given request.
     *
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     */
    public function createExpectation(Expectation $expectation)
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXPECTATIONS_URL);
        $request = (new PsrRequest())
            ->withUri($uri)
            ->withMethod('post')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StringStream(json_encode($expectation)));
        $this->checkResponse($this->connection->send($request));
    }

    /**
     * Clears all the currently configured expectations.
     */
    public function clearExpectations()
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXPECTATIONS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('delete');

        $this->checkResponse($this->connection->send($request));
    }

    /**
     * Lists all currently configured expectations.
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation[]
     */
    public function listExpectations()
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXPECTATIONS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('get');
        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 200) {
            $builder = $this->getRequestBuilder();

            return $builder->parseRequest(
                json_decode($response->getBody()->__toString(), true),
                [Expectation::class]
            );
        }

        $this->checkErrorResponse($response);
    }

    /**
     * Counts the amount of times a request was executed in phiremock.
     *
     * @param \Mcustiel\Phiremock\Client\Utils\RequestBuilder $requestBuilder
     *
     * @return int
     */
    public function countExecutions(RequestBuilder $requestBuilder)
    {
        $expectation = $requestBuilder->build();
        $expectation->setResponse(new Response());
        $uri = $this->createBaseUri()->withPath(self::API_EXECUTIONS_URL);

        $request = (new PsrRequest())
            ->withUri($uri)
            ->withMethod('post')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StringStream(json_encode($expectation)));

        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 200) {
            $json = json_decode($response->getBody()->__toString());

            return $json->count;
        }

        $this->checkErrorResponse($response);
    }

    /**
     * Resets all the scenarios to start state.
     */
    public function resetScenarios()
    {
        $uri = $this->createBaseUri()->withPath(self::API_SCENARIOS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('delete');

        $this->checkResponse($this->connection->send($request));
    }

    /**
     * Resets all the requests counters to 0.
     */
    public function resetRequestsCounter()
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXECUTIONS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('delete');

        $this->checkResponse($this->connection->send($request));
    }

    /**
     * Inits the fluent interface to create an expectation.
     *
     * @param \Mcustiel\Phiremock\Client\Utils\RequestBuilder $requestBuilder
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ExpectationBuilder
     */
    public static function on(RequestBuilder $requestBuilder)
    {
        return new ExpectationBuilder($requestBuilder);
    }

    private function createBaseUri()
    {
        return (new Uri())
            ->withScheme('http')
            ->withHost($this->host)
            ->withPort($this->port);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function checkResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() === 201) {
            return;
        }

        $this->checkErrorResponse($response);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \RuntimeException
     */
    private function checkErrorResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 500) {
            $error = json_decode($response->getBody()->__toString())->details;
            throw new \RuntimeException('An error occurred creating the expectation: ' . $error);
        }

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Request error while creating the expectation');
        }
    }

    private function getRequestBuilder()
    {
        if ($this->simpleRequestBuilder === null) {
            $this->simpleRequestBuilder = RequestBuilderFactory::createRequestBuilder();
        }

        return $this->simpleRequestBuilder;
    }
}
