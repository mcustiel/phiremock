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
use Mcustiel\Phiremock\Server\Utils\ExpectationSearchers\Helpers\IterableParallelProcessing;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Psr\Log\LoggerInterface;
use Mcustiel\Phiremock\Domain\Expectation;

class ParallelExpectationSearcher extends  ExpectationSearcher
{
    /** @var IterableParallelProcessing */
    private $parallelProcessor;

    public function __construct(
        ExpectationStorage $storage,
        RequestExpectationComparator $comparator,
        LoggerInterface $logger
    ) {
        parent::__construct($storage, $comparator, $logger);
        $this->parallelProcessor = new IterableParallelProcessing(4);
    }

    public function searchExpectation(ServerRequestInterface $request)
    {
        $result = $this->parallelProcessor->execute(
            $this->getAllExpectations(),
            function ($index, Expectation $expectation) use ($request) {
                if ($this->compare($request, $expectation)) {
                    return $expectation;
                }
                return null;
            }
        );
        $this->getLogger()->debug(var_export($result, true));
        return $this->returnExpectationWithHighestPriority($result);
    }

    private function returnExpectationWithHighestPriority($result)
    {
        $lastFound = null;
        foreach ($result as $possibleFoundExpectation) {
            if ($possibleFoundExpectation !== null) {
                if (null === $lastFound || $possibleFoundExpectation->getPriority() > $lastFound->getPriority()) {
                    $lastFound = $possibleFoundExpectation;
                }
            }
        }
        return $lastFound;
    }
}
