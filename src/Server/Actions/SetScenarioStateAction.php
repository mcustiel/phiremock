<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\ScenarioState;
use Mcustiel\Phiremock\Server\Actions\Base\AbstractRequestAction;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\SimpleRequest\RequestBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SetScenarioStateAction extends AbstractRequestAction implements ActionInterface
{
    private $storage;

    public function __construct(
        RequestBuilder $requestBuilder,
        ScenarioStorage $storage,
        LoggerInterface $logger
    ) {
        parent::__construct($requestBuilder, $logger);
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $transactionData->setResponse(
            $this->processAndGetResponse(
                $transactionData,
                function (TransactionData $transaction, ScenarioState $state) {
                    $this->storage->setScenarioState($state->getScenarioName(), $state->getScenarioState());
                    $this->logger->debug(
                        'Scenario ' . $state->getScenarioName() . ' state is set to ' . $state->getScenarioState()
                    );

                    return $transaction->getResponse()
                        ->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->withBody(new StringStream(json_encode($state)));
                }
            )
        );
    }

    /**
     * @return \Mcustiel\Phiremock\Domain\ScenarioState
     */
    protected function parseRequestObject(ServerRequestInterface $request)
    {
        $object = $this->requestBuilder->parseRequest(
            $this->parseJsonBody($request),
            ScenarioState::class,
            RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
        );
        $this->logger->debug('Parsed scenario state: ' . var_export($object, true));

        return $object;
    }
}
