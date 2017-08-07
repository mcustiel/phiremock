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

    protected function parseJsonBody(ServerRequestInterface $request)
    {
        $bodyJson = @json_decode($request->getBody()->__toString(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        return $bodyJson;
    }

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

    protected function processAndGetResponse(TransactionData $transactionData, callable $process)
    {
        try {
            return $this->createExpectationFromRequestAndProcess($transactionData, $process);
        } catch (InvalidRequestException $e) {
            $this->logger->warning('Invalid request received');

            return $this->constructErrorResponse($e->getErrors(), $transactionData->getResponse());
        } catch (\Exception $e) {
            $this->logger->warning('An unexpected exception occurred: ' . $e->getMessage());

            return $this->constructErrorResponse([$e->getMessage()], $transactionData->getResponse());
        }
    }

    private function createExpectationFromRequestAndProcess(
        TransactionData $transactionData,
        callable $process
    ) {
        /**
         * @var \Mcustiel\Phiremock\Domain\Expectation
         */
        $expectation = $this->requestBuilder->parseRequest(
            $this->parseJsonBody($transactionData->getRequest()),
            Expectation::class,
            RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
        );
        $this->logger->debug('Parsed expectation: ' . var_export($expectation, true));

        return $process($transactionData, $expectation);
    }
}
