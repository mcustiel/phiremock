<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\SimpleRequest\RequestBuilder;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Common\StringStream;
use Psr\Log\LoggerInterface;
use Mcustiel\Phiremock\Server\Actions\Base\AbstractRequestAction;
use Psr\Http\Message\ResponseInterface;

class AddExpectationAction extends AbstractRequestAction implements ActionInterface
{
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ExpectationStorage
     */
    private $storage;

    public function __construct(
        RequestBuilder $requestBuilder,
        ExpectationStorage $storage,
        LoggerInterface $logger
    ) {
        parent::__construct($requestBuilder, $logger);
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $transactionData->setResponse(
            $this->processAndGetResponse(
                $transactionData,
                function (TransactionData $transaction, Expectation $expectation) {
                    $this->validateExpectationOrThrowException($expectation, $this->logger);
                    $this->storage->addExpectation($expectation);
                    return $this->constructResponse([], $transaction->getResponse());
                }
            )
        );
    }

    private function constructResponse(array $listOfErrors, ResponseInterface $response)
    {
        if (empty($listOfErrors)) {
            return $response->withStatus(201)->withBody(new StringStream('{"result" : "OK"}'));
        }
        return $this->constructErrorResponse($listOfErrors, $response);
    }
}
