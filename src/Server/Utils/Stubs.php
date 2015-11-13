<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Server\Domain\Expectation;

class Stubs
{
    /**
     * @var array
     */
    private $config;

    public function __construct()
    {
        $this->reset();
    }

    public function addStub(Expectation $expectation)
    {
        $max = count($this->config);
        $this->config[$max] = $this->config[$max - 1];
        $this->config[$max - 1] = $this->generateConfig($expectation, $max);
    }

    private function generateConfig(Expectation $expectation)
    {
        $config = [];
        $config['condition'] = [];
        if (!empty($expectation->getRequest()->getMethod())) {
            $config['condition']['input-source'] = [
                $expectation->getRequest()->getMethod() => null
            ];
        }
    }

    public function reset()
    {
        $this->config = [
            0 => [
                'condition' => [],
                'if-matches' => [
                    'not-found' => null
                ]
            ]
        ];
    }

    public function getRouterConfig()
    {
        return [
            'start' => 0,
            'nodes' => $this->config
        ];
    }
}
