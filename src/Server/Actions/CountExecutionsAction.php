<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Zend\Diactoros\Stream;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;

class CountExecutionsAction implements ActionInterface
{
    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $requestBuilder;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $storage;
    /**
     * @var \Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator
     */
    private $comparator;

    public function __construct(
        RequestBuilder $requestBuilder,
        ExpectationStorage $storage,
        RequestExpectationComparator $comparator
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->storage = $storage;
        $this->comparator = $comparator;
    }

    /**
     * {@inheritDoc}
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $listOfErrors = [];
        try {
            /**
             * @var \Mcustiel\Phiremock\Domain\Expectation $expectation
             */
            $expectation = $this->requestBuilder->parseRequest(
                $this->parseJsonBody($transactionData->getRequest()),
                Expectation::class,
                RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
            );
            if ($this->requestIsInvalid($expectation->getRequest())) {
                throw new \RuntimeException('Invalid request specified to verify');
            }
            $count = $this->searchForExecutionsCount($expectation);
            echo "$count executions found\n";
            $transactionData->setResponse(
                $transactionData->getResponse()->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(new Stream('data://text/plain,' . json_encode(['count' => $count])))
            );
            return;
        } catch (\Mcustiel\SimpleRequest\Exception\InvalidRequestException $e) {
            echo "Invalid request: " , $e->__toString();
            $listOfErrors = $e->getErrors();
        } catch (\Exception $e) {
            echo "Exception: " , $e->__toString();
            $listOfErrors = [$e->getMessage()];
        }

        $transactionData->setResponse(
            $this->constructErrorResponse($listOfErrors, $transactionData->getResponse())
        );
    }

    private function searchForExecutionsCount(Expectation $request)
    {
        $foundPosition = -1;
        $lastFound = null;
        foreach ($this->storage->listExpectations() as $position => $expectation) {
            if ($this->compareExpectations($request, $expectation)) {
                if ($lastFound == null || $expectation->getPriority() > $lastFound->getPriority()) {
                    $foundPosition = $position;
                    $lastFound = $lastFound;
                }
            }
        }
        return $foundPosition >= 0 ? $this->storage->getExpectationUses($foundPosition) : 0;
    }

    private function compareExpectations(Expectation $expectation1, Expectation $expectation2)
    {
        return $expectation1->getScenarioName() == $expectation2->getScenarioName()
            && $expectation1->getScenarioStateIs() == $expectation2->getScenarioStateIs()
            && $this->compareRequests($expectation1->getRequest(), $expectation2->getRequest());
    }

    private function compareRequests(Request $request1, Request $request2)
    {
        return $request1->getMethod() == $request2->getMethod()
            && (($request1->getBody() == null && $request2->getBody() == null)
            || $request1->getBody()->getMatcher() == $request2->getBody()->getMatcher()
            || $request1->getBody()->getValue() == $request2->getBody()->getValue())
            && (($request1->getHeaders() == null && $request2->getHeaders() == null)
            ||$request1->getHeaders()->getMatcher() == $request2->getHeaders()->getMatcher()
            || $request1->getHeaders()->getValue() == $request2->getHeaders()->getValue())
            && (($request1->getUrl() == null && $request2->getUrl())
            || $request1->getUrl()->getMatcher() == $request2->getUrl()->getMatcher()
            || $request1->getUrl()->getValue() == $request2->getUrl()->getValue());
    }

    private function responseIsInvalid($response)
    {
        return empty($response->getStatusCode());
    }

    private function requestIsInvalid($request)
    {
        return empty($request->getBody()) && empty($request->getHeaders())
            && empty($request->getMethod()) && empty($request->getUrl());
    }

    private function constructErrorResponse($listOfErrors, $response)
    {
        $statusCode = 500;
        $body = '{"result" : "ERROR", "details" : ' . json_encode($listOfErrors) . '}';

        return $response->withStatus($statusCode)->withBody(new Stream("data://text/plain,{$body}"));
    }

    private function parseJsonBody($request)
    {
        $bodyJson = @json_decode($request->getBody()->__toString(), true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        return $bodyJson;
    }
}
