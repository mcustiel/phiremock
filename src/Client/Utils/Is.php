<?php
namespace Mcustiel\Phiremock\Client\Utils;

use Mcustiel\Phiremock\Domain\Condition;

class Is
{
    public static function equalTo($value)
    {
        return new Condition('isEqualTo', $value);
    }

    public static function matching($value)
    {
        return new Condition('matches', $value);
    }

    public static function sameStringAs($value)
    {
        return new Condition('isSameString', $value);
    }
}
