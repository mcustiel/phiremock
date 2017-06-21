<?php

namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\PowerRoute\Common\TransactionData;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Uri;

class ProxyResponseStrategy implements ResponseStrategyInterface
{
    /**
     * @var \Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface
     */
    private $httpService;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(RemoteConnectionInterface $httpService, LoggerInterface $logger)
    {
        $this->httpService = $httpService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Utils\Strategies\ResponseCreatorInterface::createResponse()
     */
    public function createResponse(Expectation $expectation, TransactionData $transactionData)
    {
        $url = $expectation->getProxyTo();
        $this->logger->debug('Proxying request to : ' . $url);

        return $this->httpService->send(
            $transactionData->getRequest()->withUri(new Uri($url))
        );
    }
}
