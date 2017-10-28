<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;
use Mcustiel\SimpleRequest\RequestBuilder;
use Psr\Log\LoggerInterface;

class FileExpectationsLoader
{
    use ExpectationValidator;

    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $requestBuilder;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $storage;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $backup;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestBuilder $requestBuilder,
        ExpectationStorage $storage,
        ExpectationStorage $backup,
        LoggerInterface $logger
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->storage = $storage;
        $this->backup = $backup;
        $this->logger = $logger;
    }

    public function loadExpectationFromFile($fileName)
    {
        $this->logger->debug("Loading expectation file $fileName");
        $content = file_get_contents($fileName);
        $data = @json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }
        /** @var Mcustiel\Phiremock\Domain\Expectation $expectation */
        $expectation = $this->requestBuilder->parseRequest(
            $data,
            Expectation::class,
            RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
        );
        $this->validateExpectationOrThrowException($expectation, $this->logger);

        $this->logger->debug('Parsed expectation: ' . var_export($expectation, true));
        $this->storage->addExpectation($expectation);
        // As we have no API to modify expectation parsed the same object could be used for backup.
        // On futher changes when $expectation modifications are possible something like deep-copy
        // should be used to clone expectation.
        $this->backup->addExpectation($expectation);
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
}
