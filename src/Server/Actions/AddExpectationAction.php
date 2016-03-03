<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Common\StringStream;
use Psr\Log\LoggerInterface;
use Mcustiel\SimpleRequest\Exception\InvalidRequestException;

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
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestBuilder $requestBuilder,
        ExpectationStorage $storage,
        LoggerInterface $logger
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->storage = $storage;
        $this->logger = $logger;
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
            $this->validateExpectation($expectation);

            $this->logger->debug('Parsed expectation: ' . var_export($expectation, true));
            $this->storage->addExpectation($expectation);
        } catch (InvalidRequestException $e) {
            $this->logger->warning('Invalid request received');
            $listOfErrors = $e->getErrors();
        } catch (\Exception $e) {
            $this->logger->warning('An unexpected exception occurred: ' . $e->getMessage());
            $listOfErrors = [$e->getMessage()];
        }

        $transactionData->setResponse(
            $this->constructResponse($listOfErrors, $transactionData->getResponse())
        );
    }

    private function validateExpectation(Expectation $expectation)
    {
        if ($this->requestIsInvalid($expectation->getRequest())) {
            throw new \RuntimeException('Invalid request specified in expectation');
        }
        if ($this->responseIsInvalid($expectation->getResponse())) {
            throw new \RuntimeException('Invalid response specified in expectation');
        }
        $this->validateScenarioConfig($expectation);
    }

    private function validateScenarioConfig(Expectation $expectation)
    {
        if (
            !$expectation->getScenarioName()
            && ($expectation->getScenarioStateIs() || $expectation->getNewScenarioState())
        ) {
            $this->logger->error('Scenario name related misconfiguration');
            throw new \RuntimeException(
                'Expecting or trying to set scenario state without specifying scenario name'
            );
        }

        if ($expectation->getNewScenarioState() && ! $expectation->getScenarioStateIs()) {
            $this->logger->error('Scenario states misconfiguration');
            throw new \RuntimeException(
                'Trying to set scenario state without specifying scenario previous state'
            );
        }
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

    private function constructResponse($listOfErrors, $response)
    {
        if (empty($listOfErrors)) {
            $statusCode = 201;
            $body = '{"result" : "OK"}';
        } else {
            $statusCode = 500;
            $body = '{"result" : "ERROR", "details" : ' . json_encode($listOfErrors) . '}';
        }
        return $response->withStatus($statusCode)->withBody(new StringStream($body));
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
