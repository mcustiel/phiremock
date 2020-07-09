<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;

class HeadersSpecificationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createSpecificationWithOneHeaderInResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('create an specification with one header in response');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'headers' => ['Location' => '/potato.php'],
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":{"Location":"\/potato.php"},"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function createSpecificationWithMoreThanOneHeaderInResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('create an specification with several headers in response');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'headers' => [
                        'Location'      => '/potato.php',
                        'Cache-Control' => 'private, max-age=0, no-cache',
                        'Pragma'        => 'no-cache',
                    ],
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":{'
            . '"Location":"\/potato.php","Cache-Control":"private, max-age=0, no-cache",'
            . '"Pragma":"no-cache"},"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function createSpecificationWithEmptyHeadersTest(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with no headers in response');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function failOnEmptyHeadersInspecificationTest(AcceptanceTester $I)
    {
        $I->wantTo('fail when creating a specification with invalid headers');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'headers' => 'potato',
                ],
            ])
        );
        $I->seeResponseCodeIs(500);
        $I->canSeeResponseEquals('{"result" : "ERROR", "details" : ["Response headers are invalid: \'potato\'"]}');
    }
}
