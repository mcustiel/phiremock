<?php
namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Mcustiel\PowerRoute\Actions\NotFound;
use Mcustiel\PowerRoute\Common\ArgumentAware;
use Zend\Diactoros\Stream;

class VerifyRequestFound implements ActionInterface
{
    use ArgumentAware;

    public function execute(TransactionData $transactionData)
    {
        /**
         * @var \Mcustiel\Phiremock\Server\Domain\Response $foundResponse
         */
        $foundResponse = $transactionData->get('foundResponse');
        if (!$foundResponse) {
            (new NotFound())->execute($transactionData);
            return;
        }
        /**
         * @var \Psr\Http\Message\ResponseInterface $response
         */
        $response = $transactionData->getResponse();
        if ($foundResponse->getBody()) {
            $response = $response->withBody(new Stream('data://text/plain,' . $foundResponse->getBody()));
        }
        if ($foundResponse->getStatusCode()) {
            $response = $response->withStatus($foundResponse->getStatusCode());
        }
        if ($foundResponse->getHeaders()) {
            foreach ($foundResponse->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            };
        }
        if ($foundResponse->getDelayMillis()) {
            usleep($foundResponse->getDelayMillis() * 1000);
        }
        $transactionData->setResponse($response);
    }
}