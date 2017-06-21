<?php

namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Condition;

class Is
{
    /**
     * @param mixed $value
     *
     * @return \Mcustiel\Phiremock\Domain\Condition
     */
    public static function equalTo($value)
    {
        return new Condition('isEqualTo', $value);
    }

    /**
     * @param string $value
     *
     * @return \Mcustiel\Phiremock\Domain\Condition
     */
    public static function matching($value)
    {
        return new Condition('matches', $value);
    }

    /**
     * @param string $value
     *
     * @return \Mcustiel\Phiremock\Domain\Condition
     */
    public static function sameStringAs($value)
    {
        return new Condition('isSameString', $value);
    }

    /**
     * @param string $value
     *
     * @return \Mcustiel\Phiremock\Domain\Condition
     */
    public static function containing($value)
    {
        return new Condition('contains', $value);
    }
}
