<?php


use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\Condition;

class ClientCest
{
    private $phiremock;

    public function _before(AcceptanceTester $I)
    {
        $this->phiremock = new PhiremockClient('127.0.0.1', '8086');
    }

    public function _after(AcceptanceTester $I)
    {

    }

    // tests
    public function shouldCreateAnExpectationTest(AcceptanceTester $I)
    {
        $expectation = new Expectation();
        $request = new Request();
        $request->setMethod('get');
        $request->setUrl(new Condition('isEqualTo', '/potato'));
        $response = new Response();
        $response->setStatusCode(201);
        $response->setBody('Tomato!');
        $expectation->setRequest($request)->setResponse($response);
        $this->phiremock->createExpectation($expectation);

        $I->sendGET('/potato');
        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Tomato!');
    }
}
