<?php

namespace Mcustiel\Phiremock\Client\Utils;

class Respond
{
    /**
     * @param int $status
     *
     * @return \Mcustiel\Phiremock\Client\Utils\ResponseBuilder
     */
    public static function withStatusCode($status)
    {
        return ResponseBuilder::create($status);
    }
}
