<?php

class ProxyCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createAnExpectationWithProxyToTest(AcceptanceTester $I)
    {
        $I->wantTo('create a specification that checks url using matches');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'scenarioName'    => 'PotatoScenario',
                'scenarioStateIs' => 'Scenario.START',
                'request'         => [
                    'method'  => 'post',
                    'url'     => ['isEqualTo' => '/potato'],
                    'body'    => ['isEqualTo' => '{"key": "This is the body"}'],
                    'headers' => ['X-Potato' => ['isSameString' => 'bAnaNa']],
                ],
                'proxyTo' => 'https://www.w3schools.com/html/',
            ]
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":"PotatoScenario","scenarioStateIs":"Scenario.START",'
            . '"newScenarioState":null,"request":{"method":"post","url":{"isEqualTo":"\/potato"},'
            . '"body":{"isEqualTo":"{\"key\": \"This is the body\"}"},"headers":{"X-Potato":'
            . '{"isSameString":"bAnaNa"}}},"response":null,'
            . '"proxyTo":"https:\/\/www.w3schools.com\/html\/","priority":0}]'
        );
    }

    public function proxyToGivenUriTest(AcceptanceTester $I)
    {
        $realUrl = 'https://www.w3schools.com/html/';

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(
                '/__phiremock/expectations',
                [
                    'scenarioName'    => 'PotatoScenario',
                    'scenarioStateIs' => 'Scenario.START',
                    'request'         => [
                        'url'    => ['isEqualTo' => '/potato'],
                        'header' => ['X-Potato' => ['isSameString' => 'bAnaNa']],
                    ],
                    'proxyTo' => $realUrl,
                ]
            );

        $guzzle = new GuzzleHttp\Client();
        $originalBody = $guzzle->get($realUrl)->getBody()->__toString();

        $I->haveHttpHeader('X-Potato', 'banana');
        $I->sendGet('/potato');
        $I->seeResponseEquals($originalBody);
    }
}
