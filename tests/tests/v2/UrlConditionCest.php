<?php

namespace Mcustiel\Phiremock\Tests\V2;

use AcceptanceTester;
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
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
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
            '[{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function createAnExpectationUsingUrlMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks url using matches');

        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::matches(), new Pattern('/some pattern/'))
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
            '[{"request":{"method":{"isSameString":"get"},"url":{"matches":"\/some pattern\/"}},"response":{"statusCode":201,"body":""}}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo(' check if it fails when an invalid matcher is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request": {"method":{"isEqualTo":"GET"}, "url": {"potato": "/some pattern"}}, "response": {"statusCode": 201} }'
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
            '{"request": {"method":{"isEqualTo":"GET"}, "url": {"isEqualTo": null}}, "response": {"statusCode": 201} }'
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Expected string got: NULL"]}');
    }
}
