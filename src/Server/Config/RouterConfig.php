<?php

namespace Mcustiel\Phiremock\Server\Config;

class RouterConfig
{
    public static function get()
    {
        return require __DIR__ . '/router-config.php';
    }
}
