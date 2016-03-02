<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Model\RequestStorage;

class ResetRequestsCountAction implements ActionInterface
{
    private $storage;

    public function __construct(RequestStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     *
     * {@inheritDoc}
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
