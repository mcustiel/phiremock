<?php
namespace Mcustiel\Phiremock\Server\Model;

use Psr\Http\Message\ServerRequestInterface;

interface RequestStorage
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return void
     */
    public function addRequest(ServerRequestInterface $request);

    /**
     *
     * @var \Mcustiel\Phiremock\Domain\Expectation[]
     */
    public function listRequests();

    /**
     * @return void
     */
    public function clearRequests();
}
