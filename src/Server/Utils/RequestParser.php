<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\Phiremock\Server\Domain\Condition;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;

class RequestParser
{
    /**
     * @var Stubs
     */
    private $stubs;
    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    public function __construct(
        Stubs $stubs,
        MatcherFactory $matcherFactory
    ) {
        $this->stubs = $stubs;
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $scenario
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function getResponseForRequest(ServerRequestInterface $request, $scenario)
    {
        foreach ($this->stubs->getExpectations() as $expectation) {
            if ($this->expectationMatchesRequest($expectation, $request)) {
                return $expectation->getResponse();
            }
        }

        return new HtmlResponse('', 404);
    }

    private function expectationMatchesRequest(Expectation $expectation, ServerRequestInterface $request)
    {
        return strtolower($expectation->getRequest()->getMethod()) == strtolower($request->getMethod())
            && $this->headersMatches($request, $expectation->getRequest()->getHeaders())
            && $this->conditionMatches(
                $request->getBody()->__toString(),
                $expectation->getRequest()->getBody()
            )
            && $this->conditionMatches(
                $request->getUri()->__toString(),
                $expectation->getRequest()->getUrl()
            );
    }

    /**
     * @param ServerRequestInterface $request
     * @param Condition[]            $headers
     */
    private function headerMatches(ServerRequestInterface $request, array $headers)
    {
        foreach ($headers as $headerName => $headerExpectation) {
            $matcher = $this->matcherFactory->createFromConfig(
                [$headerExpectation->getMatcher() => $headerExpectation->getValue()]
            );
            if (!$matcher->match($request->getHeader($headerName))) {
                return false;
            }
        }

        return true;
    }

    private function conditionMatches($value, Condition $body)
    {
        $matcher = $this->matcherFactory->createFromConfig(
            [$body->getMatcher() => $body->getValue()]
        );
        return $matcher->match($value);
    }
}
