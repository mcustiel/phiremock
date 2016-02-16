<?php
namespace Mcustiel\Phiremock\Client\Utils;

class Respond
{
    public static function withStatusCode($status)
    {
        return ResponseBuilder::create($status);
    }
}
