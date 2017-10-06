<?php

namespace Mcustiel\Phiremock\Server\Actions\Base;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\SimpleRequest\Exception\InvalidRequestException;
use Mcustiel\SimpleRequest\RequestBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Stream;

abstract class AbstractRequestAction
{
    use ExpectationValidator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    protected $requestBuilder;

    public function __construct(
        RequestBuilder $requestBuilder,
        LoggerInterface $logger
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->logger = $logger;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function parseJsonBody(ServerRequestInterface $request)
    {
        $bodyJson = @json_decode($request->getBody()->__toString(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        return $bodyJson;
    }

    /**
     * @param array                               $listOfErrors
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function constructErrorResponse(array $listOfErrors, ResponseInterface $response)
    {
        return $response->withStatus(500)
            ->withBody(
                new Stream(
                    'data://text/plain,{"result" : "ERROR", "details" : '
                    . json_encode($listOfErrors)
                    . '}'
                )
            );
    }

    /**
     * @param TransactionData $transactionData
     * @param callable        $process
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function processAndGetResponse(TransactionData $transactionData, callable $process)
    {
        try {
            return $this->createObjectFromRequestAndProcess($transactionData, $process);
        } catch (InvalidRequestException $e) {
            $this->logger->warning('Invalid request received');

            return $this->constructErrorResponse($e->getErrors(), $transactionData->getResponse());
        } catch (\Exception $e) {
            $this->logger->warning('An unexpected exception occurred: ' . $e->getMessage());

            return $this->constructErrorResponse([$e->getMessage()], $transactionData->getResponse());
        }
    }

    /**
     * @return object
     */
    protected function parseRequestObject(ServerRequestInterface $request)
    {
        /** @var \Mcustiel\Phiremock\Domain\Expectation $object */
        $object = $this->requestBuilder->parseRequest(
            $this->parseJsonBody($request),
            Expectation::class,
            RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
        );
        $this->logger->debug('Parsed expectation: ' . var_export($object, true));

        return $object;
    }

    /**
     * @param TransactionData $transactionData
     * @param callable        $process
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function createObjectFromRequestAndProcess(
        TransactionData $transactionData,
        callable $process
    ) {
        $object = $this->parseRequestObject($transactionData->getRequest());

        return $process($transactionData, $object);
    }
}
