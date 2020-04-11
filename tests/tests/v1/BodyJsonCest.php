<?php

class BodyJsonCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createExpectationWithBodyJsonArrayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a JSON body defined as array');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url']
                ],
                'response' => [
                    'body' => ['foo' => 'bar']
                ]
            ]
        );
        $I->seeResponseCodeIs('201');

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":"{\"foo\":\"bar\"}","headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }
}
