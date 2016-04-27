<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Psr\Log\LoggerInterface;

class FileExpectationsLoader
{
    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $requestBuilder;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $storage;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestBuilder $requestBuilder,
        ExpectationStorage $storage,
        LoggerInterface $logger
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    public function loadExpectationFromFile($fileName)
    {
        $this->logger->debug("Loading expectation file $fileName");
        $content = file_get_contents($fileName);
        $data = @json_decode($content, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        $expectation = $this->requestBuilder->parseRequest(
            $data,
            Expectation::class,
            RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
        );
        $this->validateExpectation($expectation);

        $this->logger->debug('Parsed expectation: ' . var_export($expectation, true));
        $this->storage->addExpectation($expectation);
    }

    public function loadExpectationsFromDirectory($directory)
    {
        $this->logger->info("Loading expectations from directory $directory");
        $iterator = new \RecursiveDirectoryIterator(
            $directory,
            \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filePath = $fileInfo->getRealPath();
                if (preg_match('/\.json$/i', $filePath)) {
                    $this->loadExpectationFromFile($filePath);
                }
            }
        }
    }

    private function validateExpectation(Expectation $expectation)
    {
        if ($this->requestIsInvalid($expectation->getRequest())) {
            throw new \RuntimeException('Invalid request specified in expectation');
        }
        if ($this->responseIsInvalid($expectation->getResponse())) {
            throw new \RuntimeException('Invalid response specified in expectation');
        }
        $this->validateScenarioConfig($expectation);
    }

    private function validateScenarioConfig(Expectation $expectation)
    {
        if (!$expectation->getScenarioName()
            && ($expectation->getScenarioStateIs() || $expectation->getNewScenarioState())
        ) {
            $this->logger->error('Scenario name related misconfiguration');
            throw new \RuntimeException(
                'Expecting or trying to set scenario state without specifying scenario name'
                );
        }

        if ($expectation->getNewScenarioState() && ! $expectation->getScenarioStateIs()) {
            $this->logger->error('Scenario states misconfiguration');
            throw new \RuntimeException(
                'Trying to set scenario state without specifying scenario previous state'
                );
        }
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
}
