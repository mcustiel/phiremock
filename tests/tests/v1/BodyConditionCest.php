<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;
use Mcustiel\Phiremock\Common\Utils\ExpectationToArrayConverter;
use Mcustiel\Phiremock\Common\Utils\RequestConditionToArrayConverter;
use Mcustiel\Phiremock\Common\Utils\ResponseToArrayConverterLocator;
use Mcustiel\Phiremock\Factory;

class BodyConditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createAnExpectationUsingBodyEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using isEqualTo');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'body'    => ['isEqualTo' => 'Potato body'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"isEqualTo":"Potato body"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function createAnExpectationUsingBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'body'    => ['matches' => '/tomato (\d[^a])+/'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendPOST('/test', 'tomato 4b4n7c');
        $I->seeResponseCodeIs(201);

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"matches":"\/tomato (\\\\d[^a])+\/"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('see if request fails when an invalid matcher is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            ['request' => ['body' => ['potato' => '/some pattern/']]]
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenInvalidValueSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('check if the request fails when and invalid value is specified');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest(['request' => ['body' => ['isEqualTo' => null]]])
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition value. Expected string, got: NULL"]}');
    }

    public function responseExpectedWhenRequestBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body pattern works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'body'    => ['matches' => '/.*potato.*/'],
                ],
                'response' => [
                    'statusCode' => 201,
                    'body'       => 'Found',
                ],
            ])
        );

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'This is the potato body');

        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body equality works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'body'    => ['isEqualTo' => 'potato'],
                ],
                'response' => [
                    'statusCode' => 201,
                    'body'       => 'Found',
                ],
            ])
        );

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyCaseInsensitiveEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body case insensitive equality works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'body'    => ['isSameString' => 'pOtAtO'],
                ],
                'response' => [
                    'statusCode' => 201,
                    'body'       => 'Found',
                ],
            ])
        );

        $I->seeResponseCodeIs(201);
        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Found');
    }
}
