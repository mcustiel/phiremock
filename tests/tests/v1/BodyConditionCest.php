<?php

use Mcustiel\Phiremock\Domain\Conditions;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Conditions\Body\BodyCondition;
use Mcustiel\Phiremock\Domain\Conditions\Body\BodyMatcher;
use Mcustiel\Phiremock\Domain\Conditions\StringValue;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Common\Utils\ExpectationToArrayConverter;
use Mcustiel\Phiremock\Common\Utils\RequestConditionToArrayConverter;
use Mcustiel\Phiremock\Common\Utils\ResponseToArrayConverterLocator;
use Mcustiel\Phiremock\Factory;
use Mcustiel\Phiremock\Domain\Http\Body;

class BodyConditionCest
{
    private $converter;

    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $this->converter = new ExpectationToArrayConverter(
            new RequestConditionToArrayConverter(),
            new ResponseToArrayConverterLocator(new Factory())
        );
    }

    public function createAnExpectationUsingBodyEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using isEqualTo');
        $request = new Conditions(
            null,
            null,
            new BodyCondition(BodyMatcher::equalTo(), new StringValue('Potato body'))
        );
        $response = new HttpResponse(new StatusCode(201));
        $expectation = new Expectation($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->converter->convert($expectation));

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"isEqualTo":"Potato body"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function createAnExpectationUsingBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks body using matches');
        $request = new Conditions(
            null,
            null,
            new BodyCondition(
                BodyMatcher::matches(),
                new StringValue('/tomato (\d[^a])+/')
            )
        );
        $response = new HttpResponse(new StatusCode(201));
        $expectation = new Expectation($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->converter->convert($expectation));

        $I->sendPOST('/test', 'tomato 4b4n7c');
        $I->seeResponseCodeIs(201);

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"matches":"\/tomato (\\\\d[^a])+\/"},"headers":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        );
    }

    public function failWhenInvalidMatcherSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('see if request fails when an invalid matcher is specified');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            ['request' => ['body' => ['potato' => '/some pattern/']]]
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenInvalidValueSpecifiedTest(AcceptanceTester $I)
    {
        $I->wantTo('check if the request fails when and invalid value is specified');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            ['request' => ['body' => ['isEqualTo' => null]]]
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition value. Expected string, got: NULL"]}');
    }

    public function responseExpectedWhenRequestBodyMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body pattern works');

        $request = new Conditions(
            null,
            null,
            new BodyCondition(
                BodyMatcher::matches(),
                new StringValue('/.*potato.*/')
            )
        );
        $response = new HttpResponse(new StatusCode(200), new Body('Found'));
        $expectation = new Expectation($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->converter->convert($expectation));

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'This is the potato body');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body equality works');

        $request = new Conditions(
            null,
            null,
            new BodyCondition(
                BodyMatcher::equalTo(),
                new StringValue('potato')
            )
        );
        $response = new HttpResponse(new StatusCode(200), new Body('Found'));
        $expectation = new Expectation($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->converter->convert($expectation));

        $I->seeResponseCodeIs(201);

        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenRequestBodyCaseInsensitiveEqualsTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in request body case insensitive equality works');

        $request = new Conditions(
            null,
            null,
            new BodyCondition(
                BodyMatcher::sameString(),
                new StringValue('pOtAtO')
            )
        );
        $response = new HttpResponse(new StatusCode(200), new Body('Found'));
        $expectation = new Expectation($request, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $this->converter->convert($expectation));

        $I->seeResponseCodeIs(201);
        $I->sendPOST('/dontcare', 'potato');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }
}
