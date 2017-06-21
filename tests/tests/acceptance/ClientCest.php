<?php


use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;
use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;

class ClientCest
{
    /**
     * @var \Mcustiel\Phiremock\Client\Phiremock
     */
    private $phiremock;

    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $this->phiremock = new PhiremockClient('127.0.0.1', '8086');
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

    public function shouldCreateAnExpectationAndReceiveItInTheList(AcceptanceTester $I)
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

        $expectations = $this->phiremock->listExpectations();

        $I->assertEquals($expectation, $expectations[0]);
    }

    public function shouldListSeveralExpectations(AcceptanceTester $I)
    {
        $expectation1 = new Expectation();
        $request = new Request();
        $request->setMethod('get');
        $request->setUrl(new Condition('isEqualTo', '/potato'));
        $response = new Response();
        $response->setStatusCode(201);
        $response->setBody('Tomato!');
        $expectation1->setRequest($request)->setResponse($response);
        $this->phiremock->createExpectation($expectation1);

        $expectation2 = new Expectation();
        $request = new Request();
        $request->setMethod('get');
        $request->setUrl(new Condition('isEqualTo', '/banana'));
        $response = new Response();
        $response->setStatusCode(201);
        $response->setBody('Coconut!');
        $expectation2->setRequest($request)->setResponse($response);
        $this->phiremock->createExpectation($expectation2);

        $expectations = $this->phiremock->listExpectations();

        $I->assertTrue(gettype($expectations) === 'array');
        $I->assertEquals(2, count($expectations));
        $I->assertEquals($expectation1, $expectations[0]);
        $I->assertEquals($expectation2, $expectations[1]);
    }

    public function shouldCreateAnExpectationTestWithFluentInterface(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
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

        $expectation = PhiremockClient::on(
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

    public function countExecutionsTest(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/executions');
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

        $I->sendGET('/potato');

        $count = $this->phiremock->countExecutions(
            A::getRequest()->andUrl(Is::equalTo('/potato'))
        );
        $I->assertEquals(2, $count);
    }

    public function countExecutionsWhenNoExpectationIsSet(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/executions');

        $I->sendGET('/potato');
        $I->seeResponseCodeIs(404);
        $I->sendGET('/potato');

        $count = $this->phiremock->countExecutions(
            A::getRequest()->andUrl(Is::equalTo('/potato'))
        );
        $I->assertEquals(2, $count);
        $count = $this->phiremock->countExecutions(
            A::getRequest()->andUrl(Is::matching('~potato~'))
        );
        $I->assertEquals(2, $count);
    }

    public function containsMatcherShouldWork(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
            A::postRequest()
                ->andUrl(Is::equalTo('/potato'))
                ->andBody(Is::containing('This is the body'))
        )->then(
            Respond::withStatusCode(202)
                ->andBody('Tomato!')
                ->andDelayInMillis(2500)
                ->andHeader('X-Tomato', 'Potato-received')
        );
        $this->phiremock->createExpectation($expectation);
        $I->sendPOST('/potato', '{"key": "This is the body"}');
        $I->seeResponseCodeIs(202);
        $I->seeResponseEquals('Tomato!');
        $I->seeHttpHeader('X-Tomato', 'Potato-received');
    }

    public function fullUrlShouldBeEvaluated(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
            A::postRequest()
                ->andUrl(Is::equalTo('/potato/coconut/?tomato=123'))
                ->andBody(Is::containing('This is the body'))
        )->then(
            Respond::withStatusCode(202)
                ->andBody('Tomato!')
                ->andDelayInMillis(2500)
                ->andHeader('X-Tomato', 'Potato-received')
        );
        $this->phiremock->createExpectation($expectation);
        $I->sendPOST('/potato/coconut/?tomato=123', '{"key": "This is the body"}');
        $I->seeResponseCodeIs(202);
        $I->seeResponseEquals('Tomato!');
        $I->seeHttpHeader('X-Tomato', 'Potato-received');
    }
}
