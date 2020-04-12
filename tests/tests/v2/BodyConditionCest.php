<?php

use Mcustiel\Phiremock\Domain\Conditions\Body\BodyCondition;
use Mcustiel\Phiremock\Domain\Conditions\Body\BodyMatcher;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodCondition;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodMatcher;
use Mcustiel\Phiremock\Domain\Conditions\Pattern;
use Mcustiel\Phiremock\Domain\Conditions\StringValue;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlCondition;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlMatcher;
use Mcustiel\Phiremock\Domain\Http\Body;
use Mcustiel\Phiremock\Domain\Http\HeadersCollection;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Factory;

class BodyConditionCest
{
    /** @var \Mcustiel\Phiremock\Factory */
    private $factory;

    public function _before(AcceptanceTester $I)
    {
        $this->factory = new Factory();
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createAnExpectationUsingBodyEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using isEqualTo');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('GET')),
            null,
            new BodyCondition(BodyMatcher::equalTo(), new StringValue('Potato body'))
        );
        $response = new HttpResponse(new StatusCode(201), new Body(''), new HeadersCollection());

        $expectation = new MockConfig($request, $response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{'
            . '"request":{"method":{"isSameString":"GET"},"body":{"isEqualTo":"Potato body"}},'
            . '"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function createAnExpectationUsingBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('POST')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/test')),
            new BodyCondition(BodyMatcher::matches(), new Pattern('/tomato (?:\d[^a])+/'))
        );
        $response = new HttpResponse(new StatusCode(201), new Body(''), new HeadersCollection());
        $expectation = new MockConfig($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
        );

        $I->sendPOST('/test', 'tomato 4b4n7c');
        $I->seeResponseCodeIs(201);

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{'
            . '"request":{"method":{"isSameString":"POST"},"url":{"isEqualTo":"\/test"},"body":{"matches":"\/tomato (?:\\\\d[^a])+\/"}},'
            . '"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('see if request fails when an invalid matcher is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request": {"method": {"isEqualTo": "get"}, "body": {"potato": "/some pattern/"}}, "response": {"statusCode": 201} }'
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenInvalidValueSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('check if the request fails when and invalid value is specified');
        $I->wantTo('check if it fails when an invalid value is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request": {"method": {"isEqualTo": "get"}, "body": {"isEqualTo": null}}, "response": {"statusCode": 201} }'
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Expected string got: NULL"]}');
    }

    public function responseExpectedWhenRequestBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body pattern works');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('POST')),
            null,
            new BodyCondition(BodyMatcher::matches(), new Pattern('/.*potato.*/'))
        );
        $response = new HttpResponse(new StatusCode(200), new Body('Found'), new HeadersCollection());
        $expectation = new MockConfig($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
        );

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'This is the potato body');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body equality works');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('POST')),
            null,
            new BodyCondition(BodyMatcher::equalTo(), new StringValue('potato'))
        );
        $response = new HttpResponse(new StatusCode(200), new Body('Found'), new HeadersCollection());
        $expectation = new MockConfig($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->factory->createExpectationToArrayConverter()->convert($expectation));

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyCaseInsensitiveEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body case insensitive equality works');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('POST')),
            null,
            new BodyCondition(BodyMatcher::sameString(), new StringValue('pOtAtO'))
        );
        $response = new HttpResponse(new StatusCode(200), new Body('Found'), new HeadersCollection());
        $expectation = new MockConfig($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->factory->createExpectationToArrayConverter()->convert($expectation));

        $I->seeResponseCodeIs(201);
        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }
}
