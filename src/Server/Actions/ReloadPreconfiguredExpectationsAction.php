<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Psr\Log\LoggerInterface;

class ReloadPreconfiguredExpectationsAction implements ActionInterface
{
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $expectationStorage;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $expectationBackup;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ExpectationStorage $expectationStorage,
        ExpectationStorage $expectationBackup,
        LoggerInterface $logger
    ) {
        $this->expectationStorage = $expectationStorage;
        $this->expectationBackup = $expectationBackup;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        foreach ($this->expectationBackup->listExpectations() as $expectation) {
            $this->expectationStorage->addExpectation($expectation);
        }
        $this->logger->debug('Pre-defined expectations are restored, scenarios and requests history are cleared.');

        $transactionData->setResponse(
            $transactionData->getResponse()->withStatus(200)
        );
    }
}
