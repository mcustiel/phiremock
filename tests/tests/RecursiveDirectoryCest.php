<?php

use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;

class RecursiveDirectoryCest
{
    /**
     * @var \Mcustiel\Phiremock\Client\Phiremock
     */
    private $phiremock;

    public function _before(AcceptanceTester $I)
    {
        $this->phiremock = new PhiremockClient('127.0.0.1', '8086');
    }

    public function detectFilesRecursively(AcceptanceTester $I)
    {
        $this->phiremock->reset();

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');

        $I->sendGET('/world');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('World!');
    }
}
