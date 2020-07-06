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
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\Options\Delay;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Factory;

class DelaySpecificationCest
{
    /** @var \Mcustiel\Phiremock\Factory */
    private $factory;

    public function _before(AcceptanceTester $I)
    {
        $this->factory = new Factory();
        $I->sendDELETE('/__phiremock/expectations');
    }

    // tests
    public function createExpectationWhithValidDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid delay specification');

        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
        );
        $response = new HttpResponse(
            new StatusCode(200),
            new Body('This is the body'),
            new HeadersCollection(),
            new Delay(5000)
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
            . '"response":{"statusCode":200,"body":"This is the body","delayMillis":5000}}]'
        );
    }

    // tests
    public function failWhithNegativedDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a negative delay specification');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},'
            . '"response":{"statusCode":200,"body":"This is the body","delayMillis":-5000}}'
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Delay must be greater or equal to 0. Got: -5000"]}'
        );
    }

    // tests
    public function failWhithInvalidDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with an invalid delay specification');
        $I->sendPOST(
            '/__phiremock/expectations',
            '{"request":{"method":{"isSameString":"get"},"url":{"isEqualTo":"\/the\/request\/url"}},'
            . '"response":{"statusCode":200,"body":"This is the body","delayMillis":"potato"}}'
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Delay must be an integer. Got: string"]}'
        );
    }

    // tests
    public function mockRequestWithDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('mock a request with delay');
        $request = new RequestConditions(
            new MethodCondition(MethodMatcher::equalTo(), new StringValue('get')),
            new UrlCondition(UrlMatcher::equalTo(), new StringValue('/the/request/url'))
            );
        $response = new HttpResponse(
            new StatusCode(200),
            new Body('This is the body'),
            new HeadersCollection(),
            new Delay(2000)
            );
        $expectation = new MockConfig($request, $response);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
            );
        $I->seeResponseCodeIs(201);

        $start = microtime(true);
        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs(200);
        $I->assertGreaterThan(2000, (microtime(true) - $start) * 1000);
    }
}
