<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Zend\Diactoros\Stream;

class AddExpectationAction implements ActionInterface
{
    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $requestBuilder;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $storage;

    public function __construct(RequestBuilder $requestBuilder, ExpectationStorage $storage)
    {
        $this->requestBuilder = $requestBuilder;
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData)
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
                throw new \RuntimeException('Invalid request specified in expectation');
            }
            if ($this->responseIsInvalid($expectation->getResponse())) {
                throw new \RuntimeException('Invalid response specified in expectation');
            }
            $this->storage->addExpectation($expectation);
        } catch (\Mcustiel\SimpleRequest\Exception\InvalidRequestException $e) {
            $listOfErrors = $e->getErrors();
        } catch (\Exception $e) {
            $listOfErrors = [$e->getMessage()];
        }

        $transactionData->setResponse(
            $this->constructResponse($listOfErrors, $transactionData->getResponse())
        );
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

    /**
     * {@inheritDoc}
     * @see \Mcustiel\PowerRoute\Common\ArgumentAwareInterface::setArgument()
     */
    public function setArgument($argument)
    {
    }

    private function constructResponse($listOfErrors, $response)
    {
        if (empty($listOfErrors)) {
            $statusCode = 201;
            $body = '{"result" : "OK"}';
        } else {
            $statusCode = 500;
            $body = '{"result" : "ERROR", "details" : ' . json_encode($listOfErrors) . '}';
        }
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
