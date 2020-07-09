<?php

namespace Mcustiel\Phiremock\Tests\Common;

use CommonTester;

class RecursiveDirectoryCest
{
    public function _before(CommonTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendPOST('/__phiremock/reset');
    }

    public function detectFilesRecursively(CommonTester $I)
    {
        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');

        $I->sendGET('/world');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('World!');
    }
}
