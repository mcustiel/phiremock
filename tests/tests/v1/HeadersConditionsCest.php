<?php

class HeadersConditionsCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function creationWithOneHeaderUsingEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks one header using isEqualTo');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => ['Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded']],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Content-Type":{"isEqualTo":"application\/x-www-form-urlencoded"}}},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function creationWithOneHeaderUsingMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks one header using matches');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => ['Content-Type' => ['matches' => '/application/']],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Content-Type":{"matches":"\/application\/"}}},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function failWhenUsingInvalidMatcherTest(AcceptanceTester $I)
    {
        $I->wantTo('fail when the matcher is invalid');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => ['Content-Type' => ['potato' => '/application/']],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ]
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenUsingNullValueTest(AcceptanceTester $I)
    {
        $I->wantTo('fail when the value is null');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => ['Content-Type' => ['matches' => null]],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ]
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition value. Expected string, got: NULL"]}');
    }

    public function creationWithMoreThanOneHeaderConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks more than one header');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => [
                        'Content-Type'     => ['matches' => '/application/'],
                        'Content-Length'   => ['isEqualTo' => '25611'],
                        'Content-Encoding' => ['isSameString' => 'gzip'],
                    ],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{'
            . '"Content-Type":{"matches":"\/application\/"},'
            . '"Content-Length":{"isEqualTo":"25611"},'
            . '"Content-Encoding":{"isSameString":"gzip"}}},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function responseExpectedWhenRequestOneHeaderMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in one request header works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => [
                        'Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded'],
                    ],
                ],
                'response' => [
                    'body' => 'Found',
                ],
            ]
        );

        $I->seeResponseCodeIs(201);
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/dontcare');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenSeveralHeadersMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in several request headers works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => [
                        'Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded'],
                        'X-Potato'     => ['matches' => '/.*tomato.*/'],
                        'X-Tomato'     => ['isSameString' => 'PoTaTo'],
                    ],
                ],
                'response' => [
                    'body' => 'Found',
                ],
            ]
        );

        $I->seeResponseCodeIs(201);

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/dontcare');

        $I->seeResponseCodeIs(404);

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->haveHttpHeader('X-potato', 'a-tomato-0');
        $I->haveHttpHeader('X-tomato', 'potato');
        $I->sendGET('/dontcare');

        $I->seeResponseEquals('Found');
    }
}
