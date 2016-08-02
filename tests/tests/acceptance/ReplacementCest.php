<?php

use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;

class ReplacementCest
{
    private $phiremock;

    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $this->phiremock = new PhiremockClient('127.0.0.1', '8086');
    }

    public function createAnExpectationWithRegexReplacementFromUrl(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
            A::getRequest()->andUrl(Is::matching('/&test=(\d+)/'))
        )->then(
            Respond::withStatusCode(200)
            ->andBody('the number is ${url.1}')
        );
        $this->phiremock->createExpectation($expectation);

        $I->sendGET('/potato', ['param1' => 123, 'test' => 456]);
        $I->seeResponseCodeIs('200');
        $I->seeResponseContains('the number is 456');
    }

    public function createAnExpectationWithRegexReplacementFromBody(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
            A::postRequest()->andUrl(Is::equalTo('/potato'))
            ->andBody(Is::matching('/a tomato (\d+)/'))
        )->then(
            Respond::withStatusCode(200)
            ->andBody('the number is ${body.1}')
        );
        $this->phiremock->createExpectation($expectation);

        $I->sendPOST('/potato', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseContains('the number is 3');
    }

    public function createAnExpectationWithRegexReplacementFromBodyAndUrl(AcceptanceTester $I)
    {
        $expectation = PhiremockClient::on(
            A::postRequest()->andUrl(Is::matching('/&test=(\d+)/'))
            ->andBody(Is::matching('/a tomato (\d+)/'))
        )->then(
            Respond::withStatusCode(200)
            ->andBody('the numbers are ${url.1} and ${body.1}')
        );
        $this->phiremock->createExpectation($expectation);

        $I->sendPOST('/potato?param1=123&test=456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseContains('the numbers are 456 and 3');
    }
}
