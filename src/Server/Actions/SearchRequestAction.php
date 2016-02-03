<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\PowerRoute\Common\ArgumentAware;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;

class SearchRequestAction implements ActionInterface
{
    use ArgumentAware;

    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $storage;

    /**
     * @var \Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator
     */
    private $comparator;

    public function __construct(
        ExpectationStorage $storage,
        RequestExpectationComparator $comparator
    ) {
        $this->storage = $storage;
        $this->comparator = $comparator;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData)
    {
        echo "Exceute\n";
        $request = $transactionData->getRequest();
        var_export($this->storage);
        foreach ($this->storage->listExpectations() as $expectation) {
            if ($this->comparator->equals($request, $expectation)) {
                $transactionData->set('foundExpectation', $expectation);
                return;
            }
        }
        $transactionData->set('foundResponse', false);
    }
}
