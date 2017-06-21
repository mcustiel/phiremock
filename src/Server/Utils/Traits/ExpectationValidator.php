<?php

namespace Mcustiel\Phiremock\Server\Utils\Traits;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Domain\Response;
use Psr\Log\LoggerInterface;

trait ExpectationValidator
{
    protected function validateExpectationOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        $this->validateRequestOrThrowException($expectation, $logger);
        $this->validateResponseOrThrowException($expectation, $logger);
        $this->validateScenarioConfigOrThrowException($expectation, $logger);
    }

    protected function validateResponseOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        if ($this->responseIsInvalid($expectation->getResponse())) {
            $logger->error('Invalid response specified in expectation');
            throw new \RuntimeException('Invalid response specified in expectation');
        }
    }

    protected function validateRequestOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        if ($this->requestIsInvalid($expectation->getRequest())) {
            $logger->error('Invalid request specified in expectation');
            throw new \RuntimeException('Invalid request specified in expectation');
        }
    }

    protected function responseIsInvalid(Response $response)
    {
        return empty($response->getStatusCode());
    }

    protected function requestIsInvalid(Request $request)
    {
        return empty($request->getBody()) && empty($request->getHeaders())
        && empty($request->getMethod()) && empty($request->getUrl());
    }

    protected function validateScenarioConfigOrThrowException(
        Expectation $expectation,
        LoggerInterface $logger
    ) {
        $this->validateScenarioNameOrThrowException($expectation, $logger);
        $this->validateScenarioStateOrThrowException($expectation, $logger);
    }

    protected function validateScenarioStateOrThrowException(
        Expectation $expectation,
        LoggerInterface $logger
    ) {
        if ($expectation->getNewScenarioState() && !$expectation->getScenarioStateIs()) {
            $logger->error('Scenario states misconfiguration');
            throw new \RuntimeException(
                'Trying to set scenario state without specifying scenario previous state'
            );
        }
    }

    protected function validateScenarioNameOrThrowException(
        Expectation $expectation,
        LoggerInterface $logger
    ) {
        if (!$expectation->getScenarioName()
            && ($expectation->getScenarioStateIs() || $expectation->getNewScenarioState())
        ) {
            $logger->error('Scenario name related misconfiguration');
            throw new \RuntimeException(
                'Expecting or trying to set scenario state without specifying scenario name'
            );
        }
    }
}
