<?php

use Mcustiel\Phiremock\Server\Domain\Request;
use Mcustiel\Phiremock\Server\Domain\Response;
use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\Phiremock\Server\Domain\Condition;

class BodyConditionCest
{

    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectation');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function createAnExpectationUsingBodyEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks body using isEqualTo');
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

    // tests
    public function createAnExpectationUsingBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks body using matches');
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

    // tests
    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks body using matches');
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

    // tests
    public function failWhenInvalidValueSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks body using matches');
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
}
