<?php
namespace Mcustiel\Phiremock\Server\Utils\Traits;

use Mcustiel\Phiremock\Domain\Expectation;
use Psr\Log\LoggerInterface;

trait ExpectationValidator
{
    private function validateExpectation(Expectation $expectation, LoggerInterface $logger)
    {
        if ($this->requestIsInvalid($expectation->getRequest())) {
            $logger->error('Invalid request specified in expectation');
            throw new \RuntimeException('Invalid request specified in expectation');
        }
        if ($this->responseIsInvalid($expectation->getResponse())) {
            $logger->error('Invalid response specified in expectation');
            throw new \RuntimeException('Invalid response specified in expectation');
        }
        $this->validateScenarioConfigOrThrowException($expectation, $logger);
    }

    private function responseIsInvalid($response)
    {
        return empty($response->getStatusCode());
    }

    private function requestIsInvalid($request)
    {
        return empty($request->getBody()) && empty($request->getHeaders())
        && empty($request->getMethod()) && empty($request->getUrl());
    }

    private function validateScenarioConfigOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        $this->validateScenarioNameOrThrowException($expectation, $logger);
        $this->validateScenarioStateOrThrowException($expectation, $logger);
    }

    private function validateScenarioStateOrThrowException($expectation, $logger)
    {
        if ($expectation->getNewScenarioState() && ! $expectation->getScenarioStateIs()) {
            $logger->error('Scenario states misconfiguration');
            throw new \RuntimeException(
                'Trying to set scenario state without specifying scenario previous state'
            );
        }
    }

    private function validateScenarioNameOrThrowException($expectation, $logger)
    {
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
