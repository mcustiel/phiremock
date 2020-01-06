<?php
/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Utils\ExpectationSearchers;

use Psr\Http\Message\ServerRequestInterface;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Psr\Log\LoggerInterface;

abstract class ExpectationSearcher
{
    /** @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage */
    private $storage;
    /** @var \Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator */
    private $comparator;
    /** @var \Psr\Log\LoggerInterface */
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

    /** @return \Mcustiel\Phiremock\Domain\Expectation[] */
    protected function getAllExpectations()
    {
        return $this->storage->listExpectations();
    }

    /** @return bool */
    protected function compare(ServerRequestInterface $request, Expectation $expectation)
    {
        return $this->comparator->equals($request, $expectation);
    }

    protected function getLogger()
    {
        return $this->logger;
    }

    /** @return Expectation|null */
    abstract public function searchExpectation(ServerRequestInterface $request);
}
