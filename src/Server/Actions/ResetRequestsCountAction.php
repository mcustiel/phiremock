<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;

class ResetRequestsCountAction implements ActionInterface
{
    private $storage;

    public function __construct(RequestStorage $storage)
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
        $this->storage->clearRequests();

        $transactionData->setResponse(
            $transactionData->getResponse()->withStatus(200)
        );
    }
}
