<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyFactory;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Actions\NotFound;
use Mcustiel\PowerRoute\Common\TransactionData;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class VerifyRequestFound implements ActionInterface
{
    /**
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
         * @var \Mcustiel\Phiremock\Domain\Expectation
         */
        $foundExpectation = $transactionData->get('foundExpectation');
        if (!$foundExpectation) {
            (new NotFound())->execute($transactionData);

            return;
        }

        $this->processScenario($foundExpectation);

        $response = $this->responseStrategyFactory
            ->getStrategyForExpectation($foundExpectation)
            ->createResponse($foundExpectation, $transactionData);

        $this->logger->debug('Responding: ' . $this->getLoggableResponse($response));
        $transactionData->setResponse($response);
    }

    private function getLoggableResponse(ResponseInterface $response)
    {
        return $response->getStatusCode() . ' / '
            . preg_replace('|\s+|', ' ', $response->getBody()->__toString());
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $foundExpectation
     */
    private function processScenario($foundExpectation)
    {
        if ($foundExpectation->getNewScenarioState()) {
            if (!$foundExpectation->getScenarioName()) {
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
