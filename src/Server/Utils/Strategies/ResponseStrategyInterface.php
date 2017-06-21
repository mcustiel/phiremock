<?php

namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\PowerRoute\Common\TransactionData;

interface ResponseStrategyInterface
{
    /**
     * Executes the strategy configured for the given
     * response config and returns the modified http response.
     *
     * @param \Mcustiel\Phiremock\Domain\Expectation      $expectation
     * @param \Mcustiel\PowerRoute\Common\TransactionData $transactionData
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createResponse(Expectation $expectation, TransactionData $transactionData);
}
