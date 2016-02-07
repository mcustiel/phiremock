<?php

use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Condition;

class StatusCodeSpecificationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectation');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function createExpectationWithStatusCodeTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid body');
        $request = new Request();
        $request->setUrl(new Condition('isEqualTo', '/the/request/url'));
        $response = new Response();
        $response->setStatusCode(401);
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->sendGET('/__phiremock/expectation');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":401,"body":null,"headers":null,"delayMillis":null}}]'
        );
    }

    // tests
    public function createExpectationWithDefaultStatusCodeTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid body');
        $request = new Request();
        $request->setUrl(new Condition('isEqualTo', '/the/request/url'));
        $response = new Response();
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectation', $expectation);

        $I->sendGET('/__phiremock/expectation');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":null}}]'
        );
    }
}
