<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;
use Codeception\Scenario;

class SameJsonCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/reset');
    }

    public function shouldCompareJsonEvenIfStringsDiffer(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ]
        );

        $I->sendPOST('/test-json', '{"tomato" : "potato",   "a":1,    "b": null, "recursive": {   "a": "b", "array" : [ {"c":"d" }, "e" ] } }');
        $I->seeResponseCodeIs(200);
        $responseBody = $I->grabResponse();
        $I->assertEquals('It is the same', $responseBody);
    }

    public function shouldCompareJsonAndDetectTheyAreTheSameWhenFieldsOrderedDifferent(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ]
        );

        $I->sendPOST('/test-json', '{"b": null, "a":1,    "recursive": {   "array" : [ {"c":"d" }, "e" ], "a": "b" }, "tomato" : "potato" }');
        $I->seeResponseCodeIs(200);
        $responseBody = $I->grabResponse();
        $I->assertEquals('It is the same', $responseBody);
    }

    public function shouldCompareJsonAndDetectTheyAreNotTheSame(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ]
        );

        $I->sendPOST('/test-json', '{"tomato": "potato", "a": 1, "b": 0, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}');
        $I->seeResponseCodeIs(404);
    }

    // From issue #38
    public function shouldDetectTheyAreNotTheSame(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{ "foo": "1", "bar": "2"}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ]
        );
        $I->sendPOST('/test-json', '{ "foo": "1"}');
        $I->seeResponseCodeIs(404);
    }

    public function shouldWorkCorrectlyUsingTheFluentInterfaceAndAString(AcceptanceTester $I, Scenario $scenario)
    {
        $scenario->skip('Needs to be moved to a suite dedicated to the client');
        $expectation = PhiremockClient::on(
            A::postRequest()->andUrl(Is::equalTo('/test-json-object'))
                ->andBody(
                    Is::sameJsonObjectAs(
                        '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'
                    )
                )
            )->then(Respond::withStatusCode(200)->andBody('It is the same'));

        $this->phiremock->createExpectation($expectation);

        $I->sendPOST('/test-json-object', '{"tomato" : "potato",   "a":1,    "b": null, "recursive": {   "a": "b", "array" : [ {"c":"d" }, "e" ] } }');
        $I->seeResponseCodeIs(200);
        $responseBody = $I->grabResponse();
        $I->assertEquals('It is the same', $responseBody);
    }

    public function shouldWorkCorrectlyUsingTheFluentInterfaceAndAJsonSerializable(AcceptanceTester $I, Scenario $scenario)
    {
        $scenario->skip('Needs to be moved to a suite dedicated to the client');
        $expectation = PhiremockClient::on(
            A::postRequest()->andUrl(Is::equalTo('/test-json-object'))
                ->andBody(
                    Is::sameJsonObjectAs(
                        [
                            'tomato'    => 'potato',
                            'a'         => 1,
                            'b'         => null,
                            'recursive' => [
                                'a'     => 'b',
                                'array' => [
                                    ['c' => 'd'],
                                    'e',
                                ],
                            ],
                        ]
                    )
                )
            )->then(Respond::withStatusCode(200)->andBody('It is the same'));

        $this->phiremock->createExpectation($expectation);

        $I->sendPOST(
            '/test-json-object',
            '{"tomato":"potato","a":1,"b":null,"recursive":{"a":"b", "array": [ {"c":"d"}, "e" ]}}'
        );

        $I->seeResponseCodeIs(200);
        $responseBody = $I->grabResponse();
        $I->assertEquals('It is the same', $responseBody);
    }

    public function shouldFailIfConfiguredWithInvalidJson(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json-object'],
                    'body'   => ['isSameJsonObject' => 'I, am an invalid - json. string.'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ]
        );

        $I->seeResponseCodeIs(500);
        $responseBody = $I->grabResponse();
        $I->assertStringStartsWith('{"result" : "ERROR", "details" : ["Invalid json: ', $responseBody);
    }

    public function shouldNotFailIfReceivesInvalidJsonInRequest(AcceptanceTester $I)
    {
        $json = [
            'tomato'    => 'potato',
            'a'         => 1,
            'b'         => null,
            'recursive' => [
                'a'     => 'b',
                'array' => [
                    ['c' => 'd'],
                    'e',
                ],
            ],
        ];
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => json_encode($json)],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ]
        );

        $I->sendPOST(
            '/test-json-object',
            'I, am an invalid - json. string.'
        );

        $I->seeResponseCodeIs(404);
    }
}
