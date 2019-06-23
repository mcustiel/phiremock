<?php

use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\Conditions\BodyCondition;
use Mcustiel\Phiremock\Domain\Http\Body;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Domain\Http\Method;
use Mcustiel\Phiremock\Domain\Conditions\Matcher;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\Http\HeadersCollection;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Factory;
use Mcustiel\Phiremock\Domain\Conditions\UrlCondition;
use Mcustiel\Phiremock\Domain\Http\Url;

class BodyConditionCest
{
    /** @var \Mcustiel\Phiremock\Factory */
    private $factory;

    public function _before(AcceptanceTester $I)
    {
        $this->factory = new Factory();
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function createAnExpectationUsingBodyEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using isEqualTo');
        $request = new RequestConditions(
            Method::get(),
            null,
            new BodyCondition(Matcher::equalTo(), new Body('Potato body'))
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
            . '"request":{"method":"GET","body":{"isEqualTo":"Potato body"}},'
            . '"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function createAnExpectationUsingBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $request = new RequestConditions(
            Method::post(),
            new UrlCondition(Matcher::equalTo(), new Url('/test')),
            new BodyCondition(Matcher::matches(), new Body('/tomato (?:\d[^a])+/'))
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
            . '"request":{"method":"POST","url":{"isEqualTo":"\/test"},"body":{"matches":"\/tomato (?:\\\\d[^a])+\/"}},'
            . '"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('see if request fails when an invalid matcher is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request": {"method": "get", "body": {"potato": "/some pattern/"}}, "response": {"statusCode": 201} }'
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
            '{"request": {"method": "get", "body": {"isEqualTo": null}}, "response": {"statusCode": 201} }'
        );


        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Body must be a string. Got: NULL"]}');
    }

    public function responseExpectedWhenRequestBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body pattern works');
        $request = new RequestConditions(
            Method::post(),
            null,
            new BodyCondition(Matcher::matches(), new Body('/.*potato.*/'))
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
        $request = new Request();
        $request->setBody(new Condition('isEqualTo', 'potato'));
        $response = new Response();
        $response->setBody('Found');
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $expectation);

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyCaseInsensitiveEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body case insensitive equality works');
        $request = new Request();
        $request->setBody(new Condition('isSameString', 'pOtAtO'));
        $response = new Response();
        $response->setBody('Found');
        $expectation = new Expectation();
        $expectation->setRequest($request)->setResponse($response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $expectation);

        $I->seeResponseCodeIs(201);
        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }
}
