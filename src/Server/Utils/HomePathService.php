<?php

namespace Mcustiel\Phiremock\Server\Utils;

class HomePathService
{
    public function getHomePath()
    {
        $unixHome = getenv('HOME');

        if (!empty($unixHome)) {
            return $unixHome;
        }

        $windowsHome = getenv('USERPROFILE');
        if (!empty($windowsHome)) {
            return $windowsHome;
        }

        $windowsHome = getenv('HOMEPATH');
        if (!empty($windowsHome)) {
            return getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }

        throw new \Exception('Could not get the users\'s home path');
    }
}
