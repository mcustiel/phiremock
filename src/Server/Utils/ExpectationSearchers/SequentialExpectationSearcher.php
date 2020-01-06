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

class SequentialExpectationSearcher extends ExpectationSearcher
{
    public function searchExpectation(ServerRequestInterface $request)
    {
        $lastFound = null;
        foreach ($this->getAllExpectations() as $expectation) {
            $lastFound = $this->getNextMatchingExpectation($lastFound, $request, $expectation);
        }

        return $lastFound;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation|null $lastFound
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    private function getNextMatchingExpectation($lastFound, ServerRequestInterface $request, Expectation $expectation)
    {
        if ($this->compare($request, $expectation)) {
            if (null === $lastFound || $expectation->getPriority() > $lastFound->getPriority()) {
                $lastFound = $expectation;
            }
        }

        return $lastFound;
    }
}
