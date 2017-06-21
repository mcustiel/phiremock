<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;

class ClearExpectationsAction implements ActionInterface
{
    private $storage;

    public function __construct(ExpectationStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $this->storage->clearExpectations();

        $transactionData->setResponse(
            $transactionData->getResponse()->withStatus(200)
        );
    }
}
