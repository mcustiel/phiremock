<?php
namespace Mcustiel\Phiremock\Client;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Client\Http\RemoteConnectionInterface;
use Zend\Diactoros\Request as PsrRequest;
use Zend\Diactoros\Uri;
use Mcustiel\SimpleRequest\RequestBuilder as SimpleRequestBuilder;
use Mcustiel\Phiremock\Client\Http\Implementation\GuzzleConnection;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Client\Utils\ExpectationBuilder;
use Mcustiel\Phiremock\Client\Utils\RequestBuilder;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Common\StringStream;

class Phiremock
{
    const API_EXPECTATIONS_URL = '/__phiremock/expectations';
    const API_EXECUTIONS_URL = '/__phiremock/executions';
    const API_SCENARIOS_URL = '/__phiremock/scenarios';

    /**
     * @var \Mcustiel\Phiremock\Client\Http\RemoteConnectionInterface
     */
    private $connection;
    private $host;
    private $port;

    public function __construct(
        $host = 'localhost',
        $port = '8080',
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
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     * @return void
     */
    public function createExpectation(Expectation $expectation)
    {
        $json = json_encode($expectation);

        $uri = $this->createBaseUri()->withPath(self::API_EXPECTATIONS_URL);
        $request = (new PsrRequest())
            ->withUri($uri)
            ->withMethod('post')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StringStream($json));
        $this->checkResponse($this->connection->send($request));
    }

    /**
     * @return void
     */
    public function clearExpectations()
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXPECTATIONS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('delete');

        $this->checkResponse($this->connection->send($request));
    }

    /**
     * @return \Mcustiel\Phiremock\Domain\Expectation[]
     */
    public function listExpectations()
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXPECTATIONS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('get');
        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 200) {
            $json = json_decode($response->getBody()->__toString());
            $builder = new SimpleRequestBuilder();
            $return = [];
            foreach ($json as $expectationArray) {
                $return[] = $builder->parseRequest($expectationArray, Expectation::class);
            }
            return $return;
        }

        $this->checkErrorResponse($response);
    }

    public function countExecutions(RequestBuilder $requestBuilder)
    {
        $expectation = $requestBuilder->build();
        $expectation->setResponse(new Response());
        $uri = $this->createBaseUri()->withPath(self::API_EXECUTIONS_URL);
        $json = json_encode($expectation);

        $request = (new PsrRequest())
            ->withUri($uri)
            ->withMethod('post')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StringStream($json));

        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 200) {
            $json = json_decode($response->getBody()->__toString());
            return $json->count;
        }

        $this->checkErrorResponse($response);
    }

    public function resetScenarios()
    {
        $uri = $this->createBaseUri()->withPath(self::API_SCENARIOS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('delete');

        $this->checkResponse($this->connection->send($request));
    }

    public function resetRequestsCounter()
    {
        $uri = $this->createBaseUri()->withPath(self::API_EXECUTIONS_URL);
        $request = (new PsrRequest())->withUri($uri)->withMethod('delete');

        $this->checkResponse($this->connection->send($request));
    }

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

    private function checkResponse($response)
    {
        if ($response->getStatusCode() === 201) {
            return;
        }

        $this->checkErrorResponse($response);
    }

    private function checkErrorResponse($response)
    {
        if ($response->getStatusCode() >= 500) {
            $error = json_decode($response->getBody()->__toString())['details'];
            throw new \RuntimeException('An error occurred creating the expectation: ' . $error);
        }

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Request error while creating the expectation');
        }
    }
}
