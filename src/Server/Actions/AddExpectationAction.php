<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\PowerRoute\Common\AbstractArgumentAware;
use Mcustiel\Phiremock\Server\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;

class AddExpectationAction extends AbstractArgumentAware implements ActionInterface
{
    public function execute(TransactionData $transactionData)
    {
        /**
         * @var \Mcustiel\SimpleRequest\RequestBuilder $requestBuilder
         */
        $requestBuilder = $this->argument['requestBuilder'];
        $listOfErrors = [];
        try {
            $bodyJson = $this->parseJsonBody($transactionData->getRequest());
            $expectation = $requestBuilder->parseRequest(
                $bodyJson,
                Expectation::class,
                RequestBuilder::RETURN_ALL_ERRORS_IN_EXCEPTION
            );
            $this->argument['stubs']->addStub($expectation);

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
