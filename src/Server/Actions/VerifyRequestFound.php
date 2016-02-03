<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\PowerRoute\Actions\NotFound;
use Mcustiel\PowerRoute\Common\ArgumentAware;
use Zend\Diactoros\Stream;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;

class VerifyRequestFound implements ActionInterface
{
    use ArgumentAware;

    /**
     *
     * @var \Mcustiel\Phiremock\Server\Model\ScenarioStorage
     */
    private $scenarioStorage;

    public function __construct(ScenarioStorage $scenarioStorage)
    {
        $this->scenarioStorage = $scenarioStorage;
    }

    public function execute(TransactionData $transactionData)
    {
        /**
         *
         * @var \Mcustiel\Phiremock\Server\Domain\Expectation $foundExpectation
         */
        $foundExpectation = $transactionData->get('foundExpectation');
        if (! $foundExpectation) {
            (new NotFound())->execute($transactionData);
            return;
        }

        $this->processScenario($foundExpectation);
        $foundResponse = $foundExpectation->getResponse();
        $transactionData->setResponse($this->generateResponse($transactionData, $foundResponse));
    }

    /**
     *
     * @param $transactionData
     * @param $foundResponse
     */
    private function generateResponse($transactionData, $foundResponse)
    {
        /**
         *
         * @var \Psr\Http\Message\ResponseInterface $response
         */
        $response = $transactionData->getResponse();
        if ($foundResponse->getBody()) {
            $response = $response->withBody(new Stream('data://text/plain,' . $foundResponse->getBody()));
        }
        if ($foundResponse->getStatusCode()) {
            $response = $response->withStatus($foundResponse->getStatusCode());
        }
        if ($foundResponse->getHeaders()) {
            foreach ($foundResponse->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        $this->processDelay($foundResponse);

        return $response;
    }

    /**
     * @param $foundResponse
     */
    private function processDelay($foundResponse)
    {
        if ($foundResponse->getDelayMillis()) {
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
                throw new \RuntimeException('Expecting scenario state without specifying scenario name');
            }
            $this->scenarioStorage->setScenarioState($foundExpectation->getScenarioName(), $foundExpectation->getNewScenarioState());
        }
    }
}