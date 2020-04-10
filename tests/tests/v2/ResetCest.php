<?php

use Mcustiel\Phiremock\Client\Connection\Host;
use Mcustiel\Phiremock\Client\Connection\Port;
use Mcustiel\Phiremock\Client\Factory;
use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;

class ResetCest
{
    /**
     * @var \Mcustiel\Phiremock\Client\Phiremock
     */
    private $phiremock;

    public function _before(AcceptanceTester $I)
    {
        $factory = Factory::createDefault();
        $this->phiremock = $factory->createPhiremockClient(
            new Host('127.0.0.1'),
            new Port(8086)
        );
    }

    public function restoreExpectationAfterDelete(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $this->phiremock->reset();

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');
    }

    public function restoreExpectationAfterRewrite(AcceptanceTester $I)
    {
        $this->phiremock->reset();

        $expectation = PhiremockClient::on(
            A::getRequest()->andUrl(Is::equalTo('/hello'))->withPriority(1)
        )->then(
            Respond::withStatusCode(200)
                ->andBody('Bye!')
        );
        $this->phiremock->createMockConfig($expectation);

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Bye!');

        $this->phiremock->reset();

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');
    }
}
