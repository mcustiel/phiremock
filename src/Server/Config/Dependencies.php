<?php

namespace Mcustiel\Phiremock\Server\Config;

class Dependencies
{
    public static function init()
    {
        return require __DIR__ . '/dependencies-setup.php';
    }
}
