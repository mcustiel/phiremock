<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;

class StoreRequestAction implements ActionInterface
{
    /**
     * @var \Mcustiel\Phiremock\Server\Model\RequestStorage
     */
    private $requestsStorage;

    public function __construct(RequestStorage $requestsStorage)
    {
        $this->requestsStorage = $requestsStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $this->requestsStorage->addRequest($transactionData->getRequest());
    }
}
