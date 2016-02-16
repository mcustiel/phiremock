<?php


use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Client\Phiremock;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;

class ClientCest
{
    private $phiremock;

    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectation');
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

    public function shouldCreateAnExpectationTestWithFluentInterface(AcceptanceTester $I)
    {
        $expectation = Phiremock::on(
            A::postRequest()->andUrl(Is::equalTo('/potato'))
                ->andHeader('X-Potato', Is::sameStringAs('bAnaNa'))
                ->andScenarioState('PotatoScenario', 'Scenario.START')
                ->andBody(Is::equalTo('{"key": "This is the body"}'))
        )->then(
            Respond::withStatusCode(202)->andBody('Tomato!')
                ->andDelayInMillis(2500)
                ->andHeader('X-Tomato', 'Potato-received')
                ->andSetScenarioStateTo('visited')
        );
        $this->phiremock->createExpectation($expectation);

        $expectation = Phiremock::on(
            A::postRequest()->andUrl(Is::equalTo('/potato'))
                ->andHeader('X-Potato', Is::sameStringAs('bAnaNa'))
                ->andScenarioState('PotatoScenario', 'visited')
                ->andBody(Is::matching('/.*"key".*/'))
        )->then(
            Respond::withStatusCode(207)->andBody('Coconut!')
                ->andDelayInMillis(1000)
                ->andHeader('X-Tomato', 'Potato-received-again')
                ->andSetScenarioStateTo('Scenario.START')
        );
        $this->phiremock->createExpectation($expectation);

        $I->haveHttpHeader('X-Potato', 'banana');
        $start = microtime(true);
        $I->sendPOST('/potato', '{"key": "This is the body"}');
        $I->assertGreaterThan(2500, (microtime(true) - $start) * 1000);
        $I->seeResponseCodeIs(202);
        $I->seeResponseEquals('Tomato!');
        $I->seeHttpHeader('X-Tomato', 'Potato-received');

        $I->haveHttpHeader('X-Potato', 'banana');
        $start = microtime(true);
        $I->sendPOST('/potato', '{"key": "This is the body"}');
        $I->assertGreaterThan(1000, (microtime(true) - $start) * 1000);
        $I->seeResponseCodeIs(207);
        $I->seeResponseEquals('Coconut!');
        $I->seeHttpHeader('X-Tomato', 'Potato-received-again');
    }
}
