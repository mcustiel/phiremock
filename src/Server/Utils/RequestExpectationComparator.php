<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Psr\Http\Message\ServerRequestInterface;
use Mcustiel\Phiremock\Server\Domain\Request;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;

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

    public function __construct(
        MatcherFactory $matcherFactory,
        InputSourceFactory $inputSourceFactory
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->inputSourceFactory = $inputSourceFactory;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface  $httpRequest
     * @param \Mcustiel\Phiremock\Server\Domain\Request $expectedRequest
     */
    public function equals(ServerRequestInterface $httpRequest, Request $expectedRequest)
    {
        echo "Checking if request matches an expectation\n";
        $atLeastOneExecution = false;
        if ($expectedRequest->getMethod()) {
            if (!$this->requestMethodMatchesExpectation($httpRequest, $expectedRequest)) {
                echo "Method does not match\n";
                return false;
            }
            $atLeastOneExecution = true;
            echo "Method match\n";
        }
        if ($expectedRequest->getUrl()) {
            if (!$this->requestUrlMatchesExpectation($httpRequest, $expectedRequest)) {
                echo "Url does not match\n";
                return false;
            }
            $atLeastOneExecution = true;
            echo "Url match\n";
        }
        if ($expectedRequest->getBody()) {
            if (!$this->requestBodyMatchesExpectation($httpRequest, $expectedRequest)) {
                echo "Body does not match\n";
                return false;
            }
            $atLeastOneExecution = true;
            echo "Body match\n";
        }
        if ($expectedRequest->getHeaders()) {
            return $this->requestHeadersMatchExpectation($httpRequest, $expectedRequest);
        }
        return $atLeastOneExecution;
    }

    private function requestMethodMatchesExpectation($httpRequest, $expectedRequest)
    {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'method' => null
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            'isSameString' => $expectedRequest->getMethod()
        ]);
        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    private function requestUrlMatchesExpectation($httpRequest, $expectedRequest)
    {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'url' => 'path'
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            $expectedRequest->getUrl()->getMatcher() => $expectedRequest->getUrl()->getValue()
        ]);
        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    private function requestBodyMatchesExpectation($httpRequest, $expectedRequest)
    {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'body' => null
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            $expectedRequest->getBody()->getMatcher() => $expectedRequest->getBody()->getValue()
        ]);
        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    private function requestHeadersMatchExpectation($httpRequest, $expectedRequest)
    {
        foreach ($expectedRequest->getHeaders() as $header => $headerCondition) {
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

    private function evaluate($inputSource, $matcher, $httpRequest)
    {
        echo 'Input source returns: ' . $inputSource->getValue($httpRequest) . PHP_EOL;
        return $matcher->match($inputSource->getValue($httpRequest));
    }
}
