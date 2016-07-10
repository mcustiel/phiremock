<?php
namespace Mcustiel\Phiremock\Common\Http\Implementation;

use Mcustiel\Phiremock\Common\Http\RemoteConnectionInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Client as GuzzleClient;

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
     *
     * {@inheritDoc}
     *
     * @see \Mcustiel\Phiremock\Client\Http\RemoteConnectionInterface::send()
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }
}
