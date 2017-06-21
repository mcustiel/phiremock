<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;

class ListExpectationsAction implements ActionInterface
{
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
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
        $list = json_encode($this->storage->listExpectations());

        $transactionData->setResponse(
            $transactionData->getResponse()
            ->withBody(new StringStream($list))
            ->withHeader('Content-type', 'application/json')
        );
    }
}
