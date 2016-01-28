<?php
namespace Mcustiel\Phiremock\Server;

use Mcustiel\SimpleRequest\Interfaces\FilterInterface;

class HeaderParserFilter implements FilterInterface
{
    public function filter($value)
    {
        $return = [];
        if ($value instanceof \stdClass) {
            foreach((array) $value as $key => $conditionArray) {
                if (preg_match('/[a-z][a-z1-2\-]+/i', $key)) {
                    $return[$key] = [
                        $matcher => $value
                    ];
                }
            }
        }
        return $return;
    }
}
