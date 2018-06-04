<?php

namespace Mcustiel\Phiremock\Server\Matchers;

use Mcustiel\PowerRoute\Matchers\MatcherInterface;

class JsonObjectsEquals implements MatcherInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Matchers\MatcherInterface::match()
     */
    public function match($value, $argument = null)
    {
        if (is_string($value)) {
            $requestValue = $this->decodeJson($value);
        } else {
            $requestValue = $value;
        }
        $configValue = $this->decodeJson($argument);

        if (!is_array($requestValue) || !is_array($configValue)) {
            return false;
        }

        return $this->areRecursivelyEquals($requestValue, $configValue);
    }

    private function decodeJson($value)
    {
        $decodedValue = json_decode($value, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('JSON parsing error: ' . json_last_error_msg());
        }

        return $decodedValue;
    }

    private function areRecursivelyEquals(array $array1, array $array2)
    {
        foreach ($array1 as $key => $value1) {
            if (!array_key_exists($key, $array2)) {
                return false;
            }
            if (gettype($value1) !== gettype($array2[$key])) {
                return false;
            }
            if (is_array($value1)) {
                if (!$this->areRecursivelyEquals($value1, $array2[$key])) {
                    return false;
                }
            } else {
                if ($value1 !== $array2[$key]) {
                    return false;
                }
            }
        }

        return true;
    }
}
