<?php

namespace McustielPhiremockTestsV1;

use AcceptanceTester;

class ResetCest
{
    public function restoreExpectationAfterDelete(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendPOST('/__phiremock/reset');

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');
    }

    public function restoreExpectationAfterRewrite(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/reset');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['isEqualTo' => '/hello'],
                ],
                'response' => [
                    'statusCode' => 200,
                    'body'       => 'Bye!',
                ],
                'priority' => 1,
            ]
        );

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Bye!');

        $I->sendPOST('/__phiremock/reset');

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');
    }
}
