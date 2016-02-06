<?php


use Mcustiel\Phiremock\Server\Domain\Request;
use Mcustiel\Phiremock\Server\Domain\Response;
use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\Phiremock\Server\Domain\Condition;

class UrlConditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectation');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function createAnExpectationUsingUrlEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks url using isEqualTo');
        $request = new Request();
        $request->setUrl(new Condition('isEqualTo', '/the/request/url'));
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
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
            );
    }

    // tests
    public function createAnExpectationUsingUrlMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks url using matches');
        $request = new Request();
        $request->setUrl(new Condition('matches', '/some pattern/'));
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
            . '"request":{"method":null,"url":{"matches":"\/some pattern\/"},"body":null,"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
            );
    }

    // tests
    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expectation that checks url using matches');
        $request = new Request();
        $request->setUrl(new Condition('potato', '/some pattern/'));
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
        $I->wantTo('Check if can create an expectation that checks url using matches');
        $request = new Request();
        $request->setUrl(new Condition('isEqualTo', null));
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
