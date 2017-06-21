<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

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
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ExpectationStorage $storage,
        RequestExpectationComparator $comparator,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->comparator = $comparator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $this->logger->debug('Searching matching expectation for request');
        $request = $transactionData->getRequest();
        $this->logger->info('Request received: ' . $this->getLoggableRequest($request));
        $foundExpectation = $this->searchForMatchingExpectation($request);
        if ($foundExpectation === null) {
            $transactionData->set('foundExpectation', false);

            return;
        }
        $transactionData->set('foundExpectation', $foundExpectation);
    }

    private function searchForMatchingExpectation(ServerRequestInterface $request)
    {
        $lastFound = null;
        foreach ($this->storage->listExpectations() as $expectation) {
            $lastFound = $this->getNextMatchingExpectation($lastFound, $request, $expectation);
        }

        return $lastFound;
    }

    private function getNextMatchingExpectation($lastFound, ServerRequestInterface $request, Expectation $expectation)
    {
        if ($this->comparator->equals($request, $expectation)) {
            if ($lastFound === null || $expectation->getPriority() > $lastFound->getPriority()) {
                $lastFound = $expectation;
            }
        }

        return $lastFound;
    }

    private function getLoggableRequest(ServerRequestInterface $request)
    {
        return $request->getMethod() . ': '
            . $request->getUri()->__toString() . ' || '
                . preg_replace('|\s+|', ' ', $request->getBody()->__toString());
    }
}
