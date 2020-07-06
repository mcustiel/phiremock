<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;

class RecursiveDirectoryCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendPOST('/__phiremock/reset');
    }

    public function detectFilesRecursively(AcceptanceTester $I)
    {
        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');

        $I->sendGET('/world');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('World!');
    }
}
