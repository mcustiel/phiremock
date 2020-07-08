<?php

namespace Mcustiel\Phiremock\Tests\V2;

use AcceptanceTester;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodCondition;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodMatcher;
use Mcustiel\Phiremock\Domain\Conditions\StringValue;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlCondition;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlMatcher;
use Mcustiel\Phiremock\Domain\Http\Body;
use Mcustiel\Phiremock\Domain\Http\HeadersCollection;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Factory;

class BodySpecificationCest
{
    /** @var \Mcustiel\Phiremock\Factory */
    private $factory;

    public function _before(AcceptanceTester $I)
    {
        $this->factory = new Factory();
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createExpectationWithBodyResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid body');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
        );
        $response = new HttpResponse(
            new StatusCode(200),
            new Body('This is the body'),
            new HeadersCollection()
        );
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
            '[{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},'
            . '"response":{"statusCode":200,"body":"This is the body"}}]'
        );
    }

    public function createWithEmptyBodyTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with an empty body');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
            );
        $response = new HttpResponse(
            new StatusCode(200),
            Body::createEmpty(),
            new HeadersCollection()
        );
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
            '[{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},'
            . '"response":{"statusCode":200,"body":""}}]'
        );
    }
}
