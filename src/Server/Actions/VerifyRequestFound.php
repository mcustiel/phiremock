<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\PowerRoute\Actions\NotFound;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\Phiremock\Common\StringStream;
use Psr\Http\Message\ResponseInterface;
use Mcustiel\Phiremock\Domain\Response;
use Psr\Log\LoggerInterface;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyFactory;

class VerifyRequestFound implements ActionInterface
{
    /**
     *
     * @var \Mcustiel\Phiremock\Server\Model\ScenarioStorage
     */
    private $scenarioStorage;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Mcustiel\Phiremock\Server\Utils\ResponseStrategyFactory
     */
    private $responseStrategyFactory;

    public function __construct(
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger,
        ResponseStrategyFactory $responseStrategyFactory
    ) {
        $this->scenarioStorage = $scenarioStorage;
        $this->logger = $logger;
        $this->responseStrategyFactory = $responseStrategyFactory;
    }

    public function execute(TransactionData $transactionData, $argument = null)
    {
        /**
         *
         * @var \Mcustiel\Phiremock\Domain\Expectation $foundExpectation
         */
        $foundExpectation = $transactionData->get('foundExpectation');
        if (! $foundExpectation) {
            (new NotFound())->execute($transactionData);
            return;
        }

        $this->processScenario($foundExpectation);
        //$foundResponse = $foundExpectation->getResponse();

        $response = $this->responseStrategyFactory
            ->getStrategyForExpectation($foundExpectation)
            ->createResponse($foundExpectation, $transactionData);

        //$response = $this->generateResponse($transactionData, $foundResponse);
        $this->logger->debug('Responding: ' . $this->getLoggableResponse($response));
        $transactionData->setResponse($response);
    }

    private function getLoggableResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() . ' / '
            . preg_replace('|\s+|', ' ', $response->getBody()->__toString());
    }

    private function generateResponse(TransactionData $transactionData, Response $foundResponse)
    {
        $response = $transactionData->getResponse();
        $response = $this->getResponseWithBody($foundResponse, $response);
        $response = $this->getResponseWithStatusCode($foundResponse, $response);
        $response = $this->getResponseWithHeaders($foundResponse, $response);
        $this->processDelay($foundResponse);

        return $response;
    }

    private function getResponseWithHeaders($foundResponse, $response)
    {
        if ($foundResponse->getHeaders()) {
            foreach ($foundResponse->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        return $response;
    }

    private function getResponseWithStatusCode($foundResponse, $response)
    {
        if ($foundResponse->getStatusCode()) {
            $response = $response->withStatus($foundResponse->getStatusCode());
        }
        return $response;
    }

    private function getResponseWithBody($foundResponse, $response)
    {
        if ($foundResponse->getBody()) {
            $response = $response->withBody(new StringStream($foundResponse->getBody()));
        }
        return $response;
    }

    /**
     * @param $foundResponse
     */
    private function processDelay($foundResponse)
    {
        if ($foundResponse->getDelayMillis()) {
            $this->logger->debug(
                'Delaying the response for ' . $foundResponse->getDelayMillis() . ' milliseconds'
            );
            usleep($foundResponse->getDelayMillis() * 1000);
        }
    }

    /**
     *
     * @param $foundExpectation
     */
    private function processScenario($foundExpectation)
    {
        if ($foundExpectation->getNewScenarioState()) {
            if (! $foundExpectation->getScenarioName()) {
                throw new \RuntimeException(
                    'Expecting scenario state without specifying scenario name'
                );
            }
            $this->scenarioStorage->setScenarioState(
                $foundExpectation->getScenarioName(),
                $foundExpectation->getNewScenarioState()
            );
        }
    }
}
