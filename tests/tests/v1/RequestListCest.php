<?php

namespace Mcustiel\Phiremock\Tests\V1;

use AcceptanceTester;

class RequestListCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendDELETE('/__phiremock/executions');
    }

    public function returnEmptyList(AcceptanceTester $I)
    {
        $I->sendPUT('/__phiremock/executions');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[]');
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

        $I->sendPUT('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[{"method":"GET","url":"http:\/\/localhost:8086\/the\/request\/url","headers":{"Host":["localhost:8086"],"User-Agent":["Symfony BrowserKit"],"Content-Type":["application\/json"],"Referer":["http:\/\/localhost:8086\/__phiremock\/expectations"]},"cookies":[],"body":""}]');
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

        $I->sendPUT('/__phiremock/executions', $I->getPhiremockRequest([
            'request' => [
                'url' => ['isEqualTo' => '/the/request/url'],
            ],
            'response' => [
                'statusCode' => 201,
            ],
        ]));
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[{"method":"GET","url":"http:\/\/localhost:8086\/the\/request\/url","headers":{"Host":["localhost:8086"],"User-Agent":["Symfony BrowserKit"],"Content-Type":["application\/json"],"Referer":["http:\/\/localhost:8086\/__phiremock\/expectations"]},"cookies":[],"body":""}]');
    }
}
