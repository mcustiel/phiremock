<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Zend\Diactoros\Stream;

class ListExpectationsAction implements ActionInterface
{
    private $storage;

    public function __construct(ExpectationStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData)
    {
        $list = json_encode($this->storage->listExpectations());
var_export($list);

        $transactionData->setResponse(
            $transactionData->getResponse()
            ->withBody(new Stream("data://text/plain,$list"))
            ->withHeader('Content-type', 'application/json')
        );
    }

    public function setArgument($argument)
    {
    }
}
