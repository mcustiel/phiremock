<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;

class ExpectationListCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function returnEmptyListTest(AcceptanceTester $I)
    {
        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[]');
    }

    public function returnCreatedExpectationTest(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ]
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }
}
