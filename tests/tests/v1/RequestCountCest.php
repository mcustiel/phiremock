<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;

class RequestCountCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendDELETE('/__phiremock/executions');
    }

    public function returnEmptyList(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/executions');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":0}');
    }

    public function returnAllExecutedRequest(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs('201');

        $I->sendPOST('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":1}');
    }

    public function returnExecutedRequestMatchingExpectation(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs('201');

        $I->sendPOST('/__phiremock/executions', $I->getPhiremockRequest([
            'request' => [
                'url' => ['isEqualTo' => '/the/request/url'],
            ],
            'response' => [
                'statusCode' => 201,
            ],
        ]));
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":1}');
    }
}
