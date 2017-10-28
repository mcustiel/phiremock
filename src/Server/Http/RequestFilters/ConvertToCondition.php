<?php

namespace Mcustiel\Phiremock\Server\Http\RequestFilters;

use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\SimpleRequest\Exception\FilterErrorException;
use Mcustiel\SimpleRequest\Interfaces\FilterInterface;

class ConvertToCondition implements FilterInterface
{
    public function filter($value)
    {
        if (null === $value) {
            return;
        }
        $this->checkValueIsValidOrThrowException($value);
        $matcher = key($value);
        $this->validateMatcherOrThrowException($matcher);
        $this->validateValueOrThrowException($value[$matcher]);

        return new Condition($matcher, $value[$matcher]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\Specificable::setSpecification()
     * @SuppressWarnings("unused")
     */
    public function setSpecification($specification = null)
    {
    }

    private function validateValueOrThrowException($value)
    {
        if (null === $value) {
            throw new FilterErrorException('Condition value can not be null');
        }
    }

    private function validateMatcherOrThrowException($matcher)
    {
        if (!$this->isValidCondition($matcher)) {
            throw new FilterErrorException('Invalid condition matcher specified: ' . $matcher);
        }
    }

    private function isValidCondition($matcherName)
    {
        return Matchers::EQUAL_TO === $matcherName
            || Matchers::MATCHES === $matcherName
            || Matchers::SAME_STRING === $matcherName
            || Matchers::CONTAINS === $matcherName;
    }

    private function checkValueIsValidOrThrowException($value)
    {
        if (!is_array($value) || 1 !== count($value)) {
            throw new FilterErrorException(
                'Condition parsing failed for "'
                . var_export($value, true)
                . '", it should be something like: "isEqualTo" : "a value"'
            );
        }
    }
}
