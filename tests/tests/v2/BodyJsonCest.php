<?php

namespace Mcustiel\Phiremock\Tests\V2;

use AcceptanceTester;

use Mcustiel\Phiremock\Domain\Conditions\Method\MethodCondition;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodMatcher;
use Mcustiel\Phiremock\Domain\Conditions\StringValue;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlCondition;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlMatcher;
use Mcustiel\Phiremock\Domain\Http\HeadersCollection;
use Mcustiel\Phiremock\Domain\Http\JsonBody;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Factory;

class BodyJsonCest
{
    /** @var \Mcustiel\Phiremock\Factory */
    private $factory;

    public function _before(AcceptanceTester $I)
    {
        $this->factory = new Factory();
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createExpectationWithBodyJsonArrayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a JSON body defined as array');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
        );
        $response = new HttpResponse(
            new StatusCode(200),
            new JsonBody(['foo' => 'bar']),
            new HeadersCollection()
        );
        $expectation = new MockConfig($request, $response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
        );
        $I->seeResponseCodeIs('201');

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},'
            . '"response":{"statusCode":200,"body":"{\"foo\":\"bar\"}"}}]'
        );
    }

    public function createExpectationWithBodyJsonObjectTest(AcceptanceTester $I)
    {
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
            );
        $response = new HttpResponse(
            new StatusCode(200),
            new JsonBody((object) ['foo' => 'bar']),
            new HeadersCollection()
            );
        $expectation = new MockConfig($request, $response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
        );
        $I->seeResponseCodeIs('201');

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},'
            . '"response":{"statusCode":200,"body":"{\"foo\":\"bar\"}"}}]'
            );
    }
}
