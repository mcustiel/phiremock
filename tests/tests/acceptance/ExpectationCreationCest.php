<?php

use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\Phiremock\Server\Domain\Request;
use Mcustiel\Phiremock\Server\Domain\Response;
use Mcustiel\Phiremock\Server\Domain\Condition;

class ExpectationCreationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectation');
    }

    public function _after(AcceptanceTester $I)
    {

    }

    // tests
    public function creationWithOnlyValidUrlConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expecteation that only checks url');
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
    public function creationWithOnlyValidMethodConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expecteation that only checks method');
        $request = new Request();
        $request->setMethod('post');
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
            . '"request":{"method":"post","url":null,"body":null,"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
            );
    }

    // tests
    public function creationWithOnlyValidBodyConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expecteation that only checks body');
        $request = new Request();
        $request->setBody(new Condition('matches', 'potato'));
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
            . '"request":{"method":null,"url":null,"body":{"matches":"potato"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
            );
    }

    // tests
    public function creationWithOnlyValidHeadersConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('Check if can create an expecteation that only checks headers');
        $request = new Request();
        $request->setHeaders(['Accept' => new Condition('matches', 'potato')]);
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Accept":{"matches":"potato"}}},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null}}]'
            );
    }

    // tests
    public function creationFailWhenEmptyRequestTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when request is empty');
        $response = new Response();
        $response->setStatusCode(201);
        $expectation = new Expectation();
        $expectation->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : {"request":"Field request, was set with invalid value: NULL"}}'
        );
    }

    // tests
    public function creationFailWhenEmptyResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when response is empty');
        $request = new Request();
        $request->setHeaders(['Accept' => new Condition('matches', 'potato')]);

        $expectation = new Expectation();
        $expectation->setRequest($request);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : {"response":"Field response, was set with invalid value: NULL"}}'
        );
    }
}
