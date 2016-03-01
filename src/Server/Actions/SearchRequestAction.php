<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;

class SearchRequestAction implements ActionInterface
{
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
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $request = $transactionData->getRequest();
        $foundExpectation = $this->searchForMatchingExpectation($request);
        if ($foundExpectation['expectation'] === null) {
            $transactionData->set('foundExpectation', false);
            return;
        }
        $transactionData->set('foundExpectation', $foundExpectation);
    }

    private function searchForMatchingExpectation($request)
    {
        $lastFound = null;
        foreach ($this->storage->listExpectations() as $expectation) {
            if ($this->comparator->equals($request, $expectation)) {
                if ($lastFound == null || $expectation->getPriority() > $lastFound->getPriority()) {
                    $lastFound = $expectation;
                }
            }
        }

        return $lastFound;
    }
}
