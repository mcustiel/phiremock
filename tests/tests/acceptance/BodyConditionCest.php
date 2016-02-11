<?php

use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Condition;

class BodyConditionCest
{

    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectation');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function createAnExpectationUsingBodyEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using isEqualTo');
        $request = new Request();
        $request->setBody(new Condition('isEqualTo', 'Potato body'));
        $response = new Response();
        $response->setStatusCode(201);
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->sendGET('/__phiremock/expectation');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"isEqualTo":"Potato body"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
        );
    }

    public function createAnExpectationUsingBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $request = new Request();
        $request->setBody(new Condition('matches', '/tomato pattern/'));
        $response = new Response();
        $response->setStatusCode(201);
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->sendGET('/__phiremock/expectation');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"matches":"\/tomato pattern\/"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $request = new Request();
        $request->setBody(new Condition('potato', '/some pattern/'));
        $response = new Response();
        $response->setStatusCode(201);
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenInvalidValueSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $request = new Request();
        $request->setBody(new Condition('isEqualTo', null));
        $response = new Response();
        $response->setStatusCode(201);
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Condition value can not be null"]}');
    }

    public function responseExpectedWhenRequestBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body pattern works');
        $request = new Request();
        $request->setBody(new Condition('matches', '/.*potato.*/'))
            ->setMethod('post');
        $response = new Response();
        $response->setBody('Found');
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'This is the potato body');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body equality works');
        $request = new Request();
        $request->setBody(new Condition('isEqualTo', 'potato'))
            ->setMethod('post');
        $response = new Response();
        $response->setBody('Found');
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyCaseInsensitiveEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body case insensitive equality works');
        $request = new Request();
        $request->setBody(new Condition('isSameString', 'pOtAtO'))
            ->setMethod('post');
        $response = new Response();
        $response->setBody('Found');
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs(201);
        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }
}
