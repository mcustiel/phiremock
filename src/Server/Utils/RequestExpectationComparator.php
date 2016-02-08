<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Psr\Http\Message\ServerRequestInterface;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;
use Mcustiel\PowerRoute\InputSources\InputSourceInterface;
use Mcustiel\PowerRoute\Matchers\MatcherInterface;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;

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

    public function __construct(
        MatcherFactory $matcherFactory,
        InputSourceFactory $inputSourceFactory,
        ScenarioStorage $scenarioStorage
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->inputSourceFactory = $inputSourceFactory;
        $this->scenarioStorage = $scenarioStorage;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface  $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Request $expectedRequest
     */
    public function equals(ServerRequestInterface $httpRequest, Expectation $expectation)
    {
        echo "Checking if request matches an expectation\n";
        $atLeastOneExecution = false;

        if ($expectation->getScenarioStateIs()) {
            echo "Checking scenario\n";
            if (!$expectation->getScenarioName()) {
                echo "Scenario misconfiguration\n";
                throw new \RuntimeException(
                    'Expecting scenario state without specifying scenario name'
                );
            }
            echo "Verifying scenario state\n";
            $scenarioState = $this->scenarioStorage->getScenarioState(
                $expectation->getScenarioName()
            );
            echo "Comparing $scenarioState with " . $expectation->getScenarioStateIs() . " \n";
            if ($expectation->getScenarioStateIs() != $scenarioState) {
                return false;
            }
        }

        $expectedRequest = $expectation->getRequest();

        if ($expectedRequest->getMethod()) {
            echo "Checking request\n";
            if (!$this->requestMethodMatchesExpectation($httpRequest, $expectedRequest)) {
                echo "Method does not match\n";
                return false;
            }
            $atLeastOneExecution = true;
            echo "Method match\n";
        }
        if ($expectedRequest->getUrl()) {
            echo "Checking URL\n";
            if (!$this->requestUrlMatchesExpectation($httpRequest, $expectedRequest)) {
                echo "Url does not match\n";
                return false;
            }
            $atLeastOneExecution = true;
            echo "Url match\n";
        }
        if ($expectedRequest->getBody()) {
            echo "Checking body\n";
            if (!$this->requestBodyMatchesExpectation($httpRequest, $expectedRequest)) {
                echo "Body does not match\n";
                return false;
            }
            $atLeastOneExecution = true;
            echo "Body match\n";
        }
        if ($expectedRequest->getHeaders()) {
            echo "Checking headers\n";
            return $this->requestHeadersMatchExpectation($httpRequest, $expectedRequest);
        }
        return $atLeastOneExecution;
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
            echo "$header => " . var_export($headerCondition) . PHP_EOL;
            $inputSource = $this->inputSourceFactory->createFromConfig([
                'header' => $header
            ]);
            $matcher = $this->matcherFactory->createFromConfig([
                $headerCondition->getMatcher() => $headerCondition->getValue()
            ]);
            if (!$this->evaluate($inputSource, $matcher, $httpRequest)) {
                echo "Headers do not match\n";
                return false;
            }
        }
        echo "Headers match\n";
        return true;
    }

    private function evaluate(
        InputSourceInterface $inputSource,
        MatcherInterface $matcher,
        ServerRequestInterface $httpRequest
    ) {
        echo 'Input source returns: ' . $inputSource->getValue($httpRequest) . PHP_EOL;
        return $matcher->match($inputSource->getValue($httpRequest));
    }
}
