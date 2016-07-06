<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Zend\Diactoros\Stream;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Common\StringStream;
use Psr\Log\LoggerInterface;
use Mcustiel\SimpleRequest\Exception\InvalidRequestException;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;

class CountRequestsAction implements ActionInterface
{
    use ExpectationValidator;

    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $requestBuilder;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\RequestStorage
     */
    private $requestsStorage;
    /**
     * @var \Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator
     */
    private $comparator;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestBuilder $requestBuilder,
        RequestStorage $storage,
        RequestExpectationComparator $comparator,
        LoggerInterface $logger
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->requestsStorage = $storage;
        $this->comparator = $comparator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        try {
            /**
             * @var \Mcustiel\Phiremock\Domain\Expectation $expectation
             */
            $expectation = $this->requestBuilder->parseRequest(
                $this->parseJsonBody($transactionData->getRequest()),
                Expectation::class,
                RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
            );
            $this->validateRequestOrThrowException($expectation, $this->logger);
            $count = $this->searchForExecutionsCount($expectation);
            $this->logger->debug('Found ' . $count . ' request matching the expectation');
            $transactionData->setResponse(
                $transactionData->getResponse()->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(new StringStream(json_encode(['count' => $count])))
            );
        } catch (InvalidRequestException $e) {
            $this->logger->warning('Invalid request received');
            $transactionData->setResponse(
                $this->constructErrorResponse($e->getErrors(), $transactionData->getResponse())
               );
        } catch (\Exception $e) {
            $this->logger->warning('An unexpected exception occurred: ' . $e->getMessage());
            $transactionData->setResponse(
                $this->constructErrorResponse([$e->getMessage()], $transactionData->getResponse())
            );
        }
    }

    private function searchForExecutionsCount(Expectation $expectation)
    {
        $count = 0;
        foreach ($this->requestsStorage->listRequests() as $request) {
            if ($this->comparator->equals($request, $expectation)) {
                $count++;
            }
        }

        return $count;
    }

    private function constructErrorResponse($listOfErrors, $response)
    {
        $statusCode = 500;
        $body = '{"result" : "ERROR", "details" : ' . json_encode($listOfErrors) . '}';

        return $response->withStatus($statusCode)->withBody(new Stream("data://text/plain,{$body}"));
    }

    private function parseJsonBody($request)
    {
        $bodyJson = @json_decode($request->getBody()->__toString(), true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        return $bodyJson;
    }
}
