<?php

use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;

class SetScenarioStateCest
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

    public function setScenarioState(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
            A::getRequest()->andUrl(Is::equalTo('/test'))
        )->then(
            Respond::withStatusCode(200)
            ->andBody('start')
        )->setScenarioName(
            'test-scenario'
        )->setScenarioStateIs(
            'Scenario.START'
        );
        $this->phiremock->createExpectation($expectation);

        $expectation = PhiremockClient::on(
            A::getRequest()->andUrl(Is::equalTo('/test'))
        )->then(
            Respond::withStatusCode(200)
            ->andBody('potato')
        )->setScenarioName(
            'test-scenario'
        )->setScenarioStateIs(
            'Scenario.POTATO'
        );
        $this->phiremock->createExpectation($expectation);

        $this->phiremock->setScenarioState('test-scenario', 'Scenario.POTATO');
        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('potato');

        $this->phiremock->setScenarioState('test-scenario', 'Scenario.START');
        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('start');
    }

    public function checkScenarioStateValidation(AcceptanceTester $I)
    {
        try {
            $this->phiremock->setScenarioState('', '');
        } catch (\RuntimeException $e) {
            // Do nothing.
        }
        $I->assertNotEmpty($e);
        $I->assertContains('Field scenarioName, was set with invalid value', $e->getMessage());
        $I->assertContains('Field scenarioState, was set with invalid value', $e->getMessage());
    }
}
