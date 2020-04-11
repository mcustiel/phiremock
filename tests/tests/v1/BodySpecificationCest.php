<?php

class BodySpecificationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function createExpectationWithBodyResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid body');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url']
                ],
                'response' => [
                    'body' => 'This is the body'
                ]
            ]
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":"This is the body","headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function createWithEmptyBodyTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with an empty body');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url']
                ],
                'response' => [
                    'body' => null
                ]
            ]
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
            );
    }
}
