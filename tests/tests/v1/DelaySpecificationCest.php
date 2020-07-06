<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;

class DelaySpecificationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    // tests
    public function createExpectationWhithValidDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid delay specification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => 5000,
                ],
            ]
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":5000},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function failWhithNegativedDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a negative delay specification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => -5000,
                ],
            ]
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Delay must be greater or equal to 0. Got: -5000"]}'
        );
    }

    public function failWhithInvalidDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with an invalid delay specification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => 'potato',
                ],
            ]
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Delay must be an integer. Got: string"]}'
        );
    }

    // tests
    public function mockRequestWithDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('mock a request with delay');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => 2000,
                ],
            ]
        );

        $I->seeResponseCodeIs(201);

        $start = microtime(true);
        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs(200);
        $I->assertGreaterThan(2000, (microtime(true) - $start) * 1000);
    }
}
