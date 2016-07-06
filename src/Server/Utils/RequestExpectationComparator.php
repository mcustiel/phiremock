<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Psr\Http\Message\ServerRequestInterface;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\PowerRoute\Common\Conditions\ClassArgumentObject;
use Psr\Log\LoggerInterface;

class RequestExpectationComparator
{
    /**
     * @var \Mcustiel\PowerRoute\Common\Factories\MatcherFactory
     */
    private $matcherFactory;
    /**
     * @var \Mcustiel\PowerRoute\Common\Factories\InputSourceFactory
     */
    private $inputSourceFactory;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ScenarioStorage
     */
    private $scenarioStorage;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        MatcherFactory $matcherFactory,
        InputSourceFactory $inputSourceFactory,
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->inputSourceFactory = $inputSourceFactory;
        $this->scenarioStorage = $scenarioStorage;
        $this->logger = $logger;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Expectation   $expectation
     */
    public function equals(ServerRequestInterface $httpRequest, Expectation $expectation)
    {
        $this->logger->debug('Checking if request matches an expectation');
        $atLeastOneExecution = false;

        if (!$this->isExpectedScenarioState($expectation)) {
            return false;
        }

        $expectedRequest = $expectation->getRequest();

        if ($expectedRequest->getMethod()) {
            $this->logger->debug('Checking method against expectation');
            if (!$this->requestMethodMatchesExpectation($httpRequest, $expectedRequest)) {
                return false;
            }
            $atLeastOneExecution = true;
        }
        if ($expectedRequest->getUrl()) {
            $this->logger->debug('Checking url against expectation');
            if (!$this->requestUrlMatchesExpectation($httpRequest, $expectedRequest)) {
                return false;
            }
            $atLeastOneExecution = true;
        }
        if ($expectedRequest->getBody()) {
            $this->logger->debug('Checking body against expectation');
            if (!$this->requestBodyMatchesExpectation($httpRequest, $expectedRequest)) {
                return false;
            }
            $atLeastOneExecution = true;
        }
        if ($expectedRequest->getHeaders()) {
            $this->logger->debug('Checking headers against expectation');
            return $this->requestHeadersMatchExpectation($httpRequest, $expectedRequest);
        }
        return $atLeastOneExecution;
    }

    private function isExpectedScenarioState($expectation)
    {
        if ($expectation->getScenarioStateIs()) {
            $this->checkScenarioNameOrThrowException($expectation);
            $this->logger->debug('Checking scenario state again expectation');
            $scenarioState = $this->scenarioStorage->getScenarioState(
                $expectation->getScenarioName()
            );
            if ($expectation->getScenarioStateIs() != $scenarioState) {
                return false;
            }
        }
        return true;
    }


    private function checkScenarioNameOrThrowException($expectation)
    {
        if (!$expectation->getScenarioName()) {
            throw new \RuntimeException(
                'Expecting scenario state without specifying scenario name'
            );
        }
    }

    private function requestMethodMatchesExpectation(ServerRequestInterface $httpRequest, Request $expectedRequest)
    {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'method' => null
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            'isSameString' => $expectedRequest->getMethod()
        ]);
        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    private function requestUrlMatchesExpectation(ServerRequestInterface $httpRequest, Request $expectedRequest)
    {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'url' => 'path'
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            $expectedRequest->getUrl()->getMatcher() => $expectedRequest->getUrl()->getValue()
        ]);
        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    private function requestBodyMatchesExpectation(ServerRequestInterface $httpRequest, Request $expectedRequest)
    {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'body' => null
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            $expectedRequest->getBody()->getMatcher() => $expectedRequest->getBody()->getValue()
        ]);
        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    private function requestHeadersMatchExpectation(ServerRequestInterface $httpRequest, Request $expectedRequest)
    {
        foreach ($expectedRequest->getHeaders() as $header => $headerCondition) {
            $inputSource = $this->inputSourceFactory->createFromConfig([
                'header' => $header
            ]);
            $matcher = $this->matcherFactory->createFromConfig([
                $headerCondition->getMatcher() => $headerCondition->getValue()
            ]);
            if (!$this->evaluate($inputSource, $matcher, $httpRequest)) {
                return false;
            }
        }
        return true;
    }

    private function evaluate(
        ClassArgumentObject $inputSource,
        ClassArgumentObject $matcher,
        ServerRequestInterface $httpRequest
    ) {
        return $matcher->getInstance()->match(
            $inputSource->getInstance()->getValue($httpRequest, $inputSource->getArgument()),
            $matcher->getArgument()
        );
    }
}
