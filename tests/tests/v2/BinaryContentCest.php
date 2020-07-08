<?php

namespace Mcustiel\Phiremock\Tests\V2;

use AcceptanceTester;
use Codeception\Configuration;
use Mcustiel\Phiremock\Client\Phiremock as PhiremockClient;
use Mcustiel\Phiremock\Client\Utils\A;
use Mcustiel\Phiremock\Client\Utils\Is;
use Mcustiel\Phiremock\Client\Utils\Respond;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodCondition;
use Mcustiel\Phiremock\Domain\Conditions\Method\MethodMatcher;
use Mcustiel\Phiremock\Domain\Conditions\StringValue;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlCondition;
use Mcustiel\Phiremock\Domain\Conditions\Url\UrlMatcher;
use Mcustiel\Phiremock\Domain\Http\BinaryBody;
use Mcustiel\Phiremock\Domain\Http\Header;
use Mcustiel\Phiremock\Domain\Http\HeaderName;
use Mcustiel\Phiremock\Domain\Http\HeadersCollection;
use Mcustiel\Phiremock\Domain\Http\HeaderValue;
use Mcustiel\Phiremock\Domain\Http\StatusCode;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Factory;

class BinaryContentCest extends BinaryContentCestV1
{
    /** @var \Mcustiel\Phiremock\Factory */
    private $factory;

    public function _before(AcceptanceTester $I)
    {
        $this->factory = new Factory();
        $I->sendDELETE('/__phiremock/expectations');
    }

    // tests
    public function shouldCreateAnExpectationWithBinaryResponseTest(AcceptanceTester $I)
    {
        $requestConditions = new RequestConditions(
            new MethodCondition(
                MethodMatcher::sameString(),
                new StringValue('get')
            ),
            new UrlCondition(
                UrlMatcher::equalTo(),
                new StringValue('/show-me-the-image')
            )
        );

        $responseContents = file_get_contents(Configuration::dataDir() . '/fixtures/Sparkles-12543.mp4');

        $headers = new HeadersCollection();
        $headers->setHeader(
            new Header(
                new HeaderName('Content-Type'),
                new HeaderValue('video/mp4')
            )
        );
        $headers->setHeader(
            new Header(
                new HeaderName('Content-Encoding'),
                new HeaderValue('base64')
            )
        );
        $response = new HttpResponse(
            new StatusCode(200),
            new BinaryBody($responseContents),
            $headers
        );
        $expectation = new MockConfig($requestConditions, $response);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $this->factory->createExpectationToArrayConverter()->convert($expectation)
        );
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/__phiremock/expectations');
        $I->writeDebugMessage($I->grabResponse());
//         $I->sendGET('/show-me-the-image');
//         $I->seeResponseCodeIs(200);
//         $I->seeHttpHeader('Content-Type', 'video/mp4');
//         $responseBody = $I->grabResponse();
//         $I->assertEquals($responseContents, $responseBody);
    }

    public function shouldCreateAnExpectationWithBinaryResponseUsingClientTest(AcceptanceTester $I)
    {
        $responseContents = file_get_contents(Configuration::dataDir() . '/fixtures/number-1943293_640.jpg');

        $this->phiremock->createExpectation(
            PhiremockClient::on(
                A::getRequest()->andUrl(Is::equalTo('/show-me-the-image-now'))
            )->then(
                Respond::withStatusCode(200)->andHeader('Content-Type', 'image/jpeg')->andBinaryBody($responseContents)
            )
        );

        $I->sendGET('/show-me-the-image-now');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Content-Type', 'image/jpeg');
        $responseBody = $I->grabResponse();
        $I->assertEquals($responseContents, $responseBody);
    }
}
