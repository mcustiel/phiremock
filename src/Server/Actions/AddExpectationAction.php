<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\PowerRoute\Common\ArgumentAware;
use Mcustiel\Phiremock\Server\Model\ExpectatationStorage;

class AddExpectationAction implements ActionInterface
{
    use ArgumentAware;

    /**
     * @var \Mcustiel\SimpleRequest\RequestBuilder
     */
    private $requestBuilder;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectatationStorage
     */
    private $storage;

    public function __construct(RequestBuilder $requestBuilder, ExpectatationStorage $storage)
    {
        $this->requestBuilder = $requestBuilder;
        $this->storage = $storage;
    }

    public function execute(TransactionData $transactionData)
    {
        $listOfErrors = [];
        try {
            $bodyJson = $this->parseJsonBody($transactionData->getRequest());
            $expectation = $this->requestBuilder->parseRequest(
                $bodyJson,
                Expectation::class,
                RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
            );
            var_export($expectation);
            $this->storage->addExpectation($expectation);

        } catch (\Mcustiel\SimpleRequest\Exception\InvalidRequestException $e) {
            $listOfErrors = $e->getErrors();
        } catch (\Exception $e) {
            $listOfErrors = [$e->getMessage()];
        }

        $transactionData->setResponse(
            $this->constructResponse($listOfErrors, $transactionData->getResponse())
        );
    }

    private function constructResponse($listOfErrors, $response)
    {
        if (empty($listOfErrors)) {
            $statusCode = 201;
            $body = '{"result" : "OK"}';
        } else {
            $statusCode = 500;
            $body = '{"result" : "ERROR", "details" : ' . json_encode($listOfErrors) . '}';
        }
        return $response->withStatus($statusCode)->withBody("data://text/plain,{$body}");
    }

    private function parseJsonBody($request)
    {
        $bodyJson = @json_decode($request->getBody()->__toString());
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        return $bodyJson;
    }
}
