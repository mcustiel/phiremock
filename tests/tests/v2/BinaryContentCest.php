<?php

namespace Mcustiel\Phiremock\Tests\V2;

use AcceptanceTester;
use Codeception\Configuration;
use Mcustiel\Phiremock\Tests\V1\BinaryContentCest as BinaryContentCestV1;

class BinaryContentCest extends BinaryContentCestV1
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    protected function getRequest(array $request): array
    {
        return array_merge(['version' => '2'], $request);
    }
}
