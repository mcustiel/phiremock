<?php

class SetScenarioStateCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function setScenarioState(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['isEqualTo' => '/test'],
                ],
                'response' => [
                    'body' => 'start',
                ],
                'scenarioName'    => 'test-scenario',
                'scenarioStateIs' => 'Scenario.START',
            ]
        );

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['isEqualTo' => '/test'],
                ],
                'response' => [
                    'body' => 'potato',
                ],
                'scenarioName'    => 'test-scenario',
                'scenarioStateIs' => 'Scenario.POTATO',
            ]
         );

        $I->sendPUT(
            '/__phiremock/scenarios',
            [
                'scenarioName'  => 'test-scenario',
                'scenarioState' => 'Scenario.POTATO',
            ]
        );
        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('potato');

        $I->sendPUT(
            '/__phiremock/scenarios',
            [
                'scenarioName'  => 'test-scenario',
                'scenarioState' => 'Scenario.START',
            ]
        );
        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('start');
    }

    public function checkScenarioStateValidation(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/__phiremock/scenarios', []);
        $I->seeResponseCodeIs(500);
        $I->seeResponseEquals('{"result":"ERROR","details":"Scenario name not set"}');
    }
}
