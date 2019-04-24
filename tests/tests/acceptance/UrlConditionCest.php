<?php

use Mcustiel\Phiremock\Domain\Conditions\Matcher;
use Mcustiel\Phiremock\Domain\Conditions\UrlCondition;
use Mcustiel\Phiremock\Domain\Http\Body;
use Mcustiel\Phiremock\Domain\Http\HeadersCollection;
use Mcustiel\Phiremock\Domain\Http\Method;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\Http\Url;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Factory;

class UrlConditionCest
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

    public function createAnExpectationUsingUrlEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks url using isEqualTo');
        $request = new RequestConditions(Method::get(), new UrlCondition(Matcher::equalTo(), new Url('/the/request/url')));
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
            '[{"request":{"method":"GET","url":{"isEqualTo":"\/the\/request\/url"}},"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function createAnExpectationUsingUrlMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks url using matches');
        $request = new RequestConditions(Method::get(), new UrlCondition(Matcher::matches(), new Url('/some pattern/')));
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
            '[{"request":{"method":"GET","url":{"matches":"\/some pattern\/"}},"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo(' check if it fails when an invalid matcher is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request": {"method": "get", "url": {"potato": "/some pattern"}}, "response": {"statusCode": 201} }'
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenInvalidValueSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('check if it fails when an invalid value is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request": {"method": "get", "url": {"isEqualTo": null}}, "response": {"statusCode": 201} }'
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Url must be a string. Got: NULL"]}');
    }
}
