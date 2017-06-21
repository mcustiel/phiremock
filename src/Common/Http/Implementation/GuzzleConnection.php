<?php

namespace Mcustiel\Phiremock\Common\Http\Implementation;

use GuzzleHttp\Client as GuzzleClient;
use Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface;
use Psr\Http\Message\RequestInterface;

class GuzzleConnection implements RemoteConnectionInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct(GuzzleClient $client = null)
    {
        if (!$client) {
            $client = new GuzzleClient();
        }
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Client\Http\RemoteConnectionInterface::send()
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }
}
