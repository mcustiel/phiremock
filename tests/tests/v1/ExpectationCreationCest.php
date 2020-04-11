<?php

class ExpectationCreationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function creationWithOnlyValidUrlConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks url');
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

    public function creationWithOnlyValidMethodConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks method');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
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
            . '"request":{"method":"post","url":null,"body":null,"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function creationWithOnlyValidBodyConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks body');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'body' => ['matches' => 'potato'],
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
            . '"request":{"method":null,"url":null,"body":{"matches":"potato"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function creationWithOnlyValidHeadersConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks headers');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'headers' => ['Accept' => ['matches' => 'potato']],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Accept":{"matches":"potato"}}},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function creationFailWhenEmptyRequestTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when request is empty');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ]
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Invalid request specified in expectation"]}'
        );
    }

    public function useDefaultWhenEmptyResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('When response is empty in request, default should be used');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                ],
                'response' => null,
            ]
        );
        $I->seeResponseCodeIs('201');

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":"get","url":null,"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
            );
    }

    public function creationFailWhenAnythingSentAsRequestTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when anything sent as request');

        $expectation = [
            'response' => ['statusCode' => 200],
            'request'  => ['potato' => 'tomato'],
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $expectation);

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid request specified in expectation"]}');
    }

    public function creationFailWhenAnythingSentAsResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when anything sent as response');

        $expectation = [
            'response' => 'response',
            'request'  => ['url' => ['isEqualTo' => '/tomato']],
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $expectation);

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Invalid response definition: \'response\'"]}'
        );
    }

    public function creationWithAllOptionsFilledTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with all possible option filled');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method'  => 'get',
                    'url'     => ['isEqualTo' => '/the/request/url'],
                    'body'    => ['isEqualTo' => 'the body'],
                    'headers' => [
                        'Content-Type'         => ['matches' => '/json/'],
                        'Accepts'              => ['isEqualTo' => 'application/json'],
                        'X-Some-Random-Header' => ['isEqualTo' => 'random value'],
                    ],
                ],
                'response' => [
                    'statusCode' => 201,
                    'body'       => 'Response body',
                    'headers'    => [
                        'X-Special-Header' => 'potato',
                        'Location'         => 'href://potato.tmt',
                    ],
                    'delayMillis' => 5000,
                ],
                'scenarioName'     => 'potato',
                'scenarioStateIs'  => 'tomato',
                'newScenarioState' => 'banana',
                'priority'         => 3,
            ]
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":"potato","scenarioStateIs":"tomato","newScenarioState":"banana",'
            . '"request":{'
            . '"method":"get","url":{"isEqualTo":"\/the\/request\/url"},'
            . '"body":{"isEqualTo":"the body"},'
            . '"headers":{'
            . '"Content-Type":{"matches":"\/json\/"},'
            . '"Accepts":{"isEqualTo":"application\/json"},'
            . '"X-Some-Random-Header":{"isEqualTo":"random value"}}},'
            . '"response":{'
            . '"statusCode":201,"body":"Response body","headers":{'
            . '"X-Special-Header":"potato",'
            . '"Location":"href:\/\/potato.tmt"},'
            . '"delayMillis":5000},'
            . '"proxyTo":null,"priority":3}]'
        );
    }
}
