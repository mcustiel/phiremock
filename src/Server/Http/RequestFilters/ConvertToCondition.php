<?php
namespace Mcustiel\Phiremock\Server\Http\RequestFilters;

use Mcustiel\SimpleRequest\Interfaces\FilterInterface;
use Mcustiel\SimpleRequest\Exception\FilterErrorException;
use Mcustiel\Phiremock\Server\Domain\Condition;

class ConvertToCondition implements FilterInterface
{
    public function filter($value)
    {
        if ($value === null) {
            return;
        }
        $this->checkValueIsValidOrThrowException($value);

        $matcher = key($value);
        if ($this->isValidCondition($matcher)) {
            $condition = new Condition($matcher, $value[$matcher]);
            var_export($condition);
            return $condition;
        }
        throw new FilterErrorException(
            'Invalid condition matcher specified: ' . $matcher
        );
    }

    public function setSpecification($specification = null)
    {
    }

    private function isValidCondition($matcherName)
    {
        return $matcherName == 'isEqualTo' || $matcherName == 'matches';
    }

    private function checkValueIsValidOrThrowException($value)
    {
        if (!is_array($value) || count($value) != 1) {
            throw new FilterErrorException(
                'Condition parsing failed it should be something like: "isEqualTo" : "a value"'
            );
        }
    }
}
