<?php

class ReplacementCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createAnExpectationWithRegexReplacementFromUrl(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['matches' => '/&test=(\d+)/'],
                ],
                'response' => [
                    'body' => 'the number is ${url.1}',
                ],
            ]
        );

        $I->sendGET('/potato', ['param1' => 123, 'test' => 456]);
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the number is 456');
    }

    public function createAnExpectationWithRegexFromUrlAsGroupExpression(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['matches' => '/&test=(\d+)/'],
                ],
                'response' => [
                    'body' => 'the number is ${url.1.1}',
                ],
            ]
        );

        $I->sendGET('/potato', ['param1' => 123, 'test' => 456]);
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the number is 456');
    }

    public function createAnExpectationWithRegexReplacementFromBody(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'body'   => ['matches' => '/a tomato (\d+)/'],
                ],
                'response' => [
                    'body' => 'the number is ${body.1}',
                ],
            ]
        );

        $I->sendPOST('/potato', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the number is 3');
    }

    public function createAnExpectationWithRegexFromBodyAsGroupExpression(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/potato'],
                    'body'   => ['matches' => '/a tomato (\d+)/'],
                ],
                'response' => [
                    'body' => 'the number is ${body.1.1}',
                ],
            ]
        );

        $I->sendPOST('/potato', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the number is 3');
    }

    public function createAnExpectationWithRegexReplacementFromBodyAndUrl(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '/&test=(\d+)/'],
                    'body'   => ['matches' => '/a tomato (\d+)/'],
                ],
                'response' => [
                    'body' => 'the numbers are ${url.1} and ${body.1}',
                ],
            ]
        );

        $I->sendPOST('/potato?param1=123&test=456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are 456 and 3');
    }

    public function createAnExpectationWithRegexFromBodyAndUrlAsGroupExpression(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '/&test=(\d+)/'],
                    'body'   => ['matches' => '/a tomato (\d+)/'],
                ],
                'response' => [
                    'body' => 'the numbers are ${url.1.1} and ${body.1.1}',
                ],
            ]
        );

        $I->sendPOST('/potato?param1=123&test=456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are 456 and 3');
    }

    public function createAnExpectationWithStrictRegexReplacementFromBodyAndUrl(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '~^/potato/(\d+)$~'],
                    'body'   => ['matches' => '/^this is a tomato (\d+)kg it weights$/'],
                ],
                'response' => [
                    'body' => 'the numbers are ${url.1} and ${body.1}',
                ],
            ]
        );

        $I->sendPOST('/potato/456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are 456 and 3');
    }

    public function createAnExpectationWithStrictRegexFromBodyAndUrlAsGroupExpression(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '~^/potato/(\d+)$~'],
                    'body'   => ['matches' => '/^this is a tomato (\d+)kg it weights$/'],
                ],
                'response' => [
                    'body' => 'the numbers are ${url.1.1} and ${body.1.1}',
                ],
            ]
        );

        $I->sendPOST('/potato/456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are 456 and 3');
    }

    public function createAnExpectationWithoutRegexReplacement(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '/potato/'],
                    'body'   => ['matches' => '/a tomato 3kg/'],
                ],
                'response' => [
                    'body'    => 'the numbers are ${url.1} and ${body.1.1}',
                    'headers' => ['Content-Type' => 'application/${url.1}'],
                ],
            ]
        );

        $I->sendPOST('/potato', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are ${url.1} and ${body.1.1}');
        $I->canSeeHttpHeader('Content-Type', 'application/${url.1}');
    }

    public function createAnExpectationWithRegexMatchGroupsFromUrl(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['matches' => '/[?&]\w*=(\d+)/'],
                ],
                'response' => [
                    'body' => 'you birthday is at ${url.1.1}.${url.1.2}.${url.1.3}',
                ],
            ]
        );

        $I->sendGET('/birthday', ['day' => 28, 'month' => 10, 'year' => 1991]);
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('you birthday is at 28.10.1991');
    }

    public function createAnExpectationWithRegexMatchGroupsFromBody(AcceptanceTester $I)
    {
        $request = '[{ "name": "Sarah", "alive": null }, { "name": "Ruth", "alive": true }]';

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/humans'],
                    'body'   => ['matches' => '/"name":\s*"([^"]*)"/'],
                ],
                'response' => [
                    'body' => 'first name is ${body.1.1}, second: ${body.1.2}',
                ],
            ]
        );

        $I->sendPOST('/humans', $request);
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('first name is Sarah, second: Ruth');
    }

    public function createAnExpectationWithRegexMatchGroupsFromBodyAndUrl(AcceptanceTester $I)
    {
        $request = '[{ "name": "Sarah", "alive": null }, { "name": "Ruth", "alive": true }]';
        $responseBody = 'You created two ${url.1.2}s with a min age of ${url.1.1}.' .
         'The first name is ${body.1.2}, second: ${body.1.1}';

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '%[?&]\w*=(\w+)%'],
                    'body'   => ['matches' => '/"name":\s*"([^"]*)"/'],
                ],
                'response' => [
                    'body' => $responseBody,
                ],
            ]
        );

        $I->sendPOST('/humans?minage=22&gender=female', $request);
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('You created two females with a min age of 22.' .
            'The first name is Ruth, second: Sarah');
    }

    public function createAnExpectationWithRegexMultipleMatchGroupsFromBody(AcceptanceTester $I)
    {
        $request = '[ { "name": "Sarah", "brothers": 0 },'
            . ' { "name": "Ruth", "brothers": 2 },'
            . ' { "name": "Lexi", "brothers": 23 } ]';
        $matcher = '%"name"\s*:\s*"([^"]*)",\s*"brothers"\s*:\s*(\d+)%';
        $response = '${body.1} has ${body.2} brothers, ${body.1.2} has ${body.2.2} brothers,'
            . ' ${body.1.3} has ${body.2.3} brothers';
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/humans'],
                    'body'   => ['matches' => $matcher],
                ],
                'response' => [
                    'body' => $response,
                ],
            ]
        );

        $I->sendPOST('/humans', $request);

        $expectedResponse = 'Sarah has 0 brothers, Ruth has 2 brothers, Lexi has 23 brothers';
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals($expectedResponse);
    }

    public function createAnExpectationWithoutRegexMatchGroups(AcceptanceTester $I)
    {
        $body = 'the numbers are ${url.1} or ${url.1.2} or ${url.1.1} and the ${body.1.1} or ${body.3.2}';
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '/potato/'],
                    'body'   => ['matches' => '/a tomato 3kg/'],
                ],
                'response' => [
                    'body' => $body,
                ],
            ]
        );

        $I->sendPOST('/potato', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals($body);
    }

    public function createAnExpectationWithRegexReplacementInHeaderFromUrl(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'get',
                    'url'    => ['matches' => '/&test=(\d+)/'],
                ],
                'response' => [
                    'headers' => ['X-Header' =>  'test=${url.1}'],
                ],
            ]
        );

        $I->sendGET('/potato', ['param1' => 123, 'test' => 456]);
        $I->seeResponseCodeIs('200');
        $I->canSeeHttpHeader('X-Header', 'test=456');
    }

    public function createAnExpectationWithRegexReplacementInHeaderFromBody(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/potato'],
                    'body'   => ['matches' => '/a tomato (\d+)/'],
                ],
                'response' => [
                    'headers' => ['X-Header' =>  '${body.1}'],
                ],
            ]
        );

        $I->sendPOST('/potato', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->canSeeHttpHeader('X-Header', '3');
    }

    public function createAnExpectationWithRegexReplacementInHeaderFromBodyAndUrl(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '~^/potato/(\d+)$~'],
                    'body'   => ['matches' => '/^this is a tomato (\d+)kg it weights$/'],
                ],
                'response' => [
                    'body'    => 'the numbers are ${url.1} and ${body.1}',
                    'headers' => ['X-Header' =>  'url=${url.1} body=${body.1}'],
                ],
            ]
        );

        $I->sendPOST('/potato/456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are 456 and 3');
        $I->canSeeHttpHeader('X-Header', 'url=456 body=3');
    }

    public function createAnExpectationWithRegexReplacementInHeaderAsGroupExpression(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['matches' => '~^/potato/(\d+)$~'],
                    'body'   => ['matches' => '/^this is a tomato (\d+)kg it weights$/'],
                ],
                'response' => [
                    'body'    => 'the numbers are ${url.1.1} and ${body.1.1}',
                    'headers' => ['X-Header' =>  'url=${url.1.1} body=${body.1.1}'],
                ],
            ]
        );

        $I->sendPOST('/potato/456', 'this is a tomato 3kg it weights');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('the numbers are 456 and 3');
        $I->canSeeHttpHeader('X-Header', 'url=456 body=3');
    }

    public function createAnExpectationWithRegexReplacementInHeaderWithMultipleMatchGroups(AcceptanceTester $I)
    {
        $request = '[ { "name": "Sarah", "brothers": 0 },'
            . ' { "name": "Ruth", "brothers": 2 },'
            . ' { "name": "Lexi", "brothers": 23 } ]';
        $matcher = '%"name"\s*:\s*"([^"]*)",\s*"brothers"\s*:\s*(\d+)%';

        $response = '${body.1} has ${body.2} brothers, ${body.1.2} has ${body.2.2} brothers,'
            . ' ${body.1.3} has ${body.2.3} brothers';
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/humans'],
                    'body'   => ['matches' => $matcher],
                ],
                'response' => [
                    'body'    => $response,
                    'headers' => ['X-Header' => $response],
                ],
            ]
        );

        $I->sendPOST('/humans', $request);

        $expectedResponse = 'Sarah has 0 brothers, Ruth has 2 brothers, Lexi has 23 brothers';
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals($expectedResponse);
        $I->seeHttpHeader('X-Header', $expectedResponse);
    }
}
