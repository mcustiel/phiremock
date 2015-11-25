<?php
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\Phiremock\Server\Utils\Stubs;
use Mcustiel\SimpleRequest\RequestBuilder;

$stubs = Stubs();
$requestBuilder = new RequestBuilder();

return [
    PowerRoute::CONFIG_ROOT_NODE => 'methodIsPost',
    PowerRoute::CONFIG_NODES => [
        'methodIsPost' => [
            PowerRoute::CONFIG_NODE_CONDITION => [
                PowerRoute::CONFIG_NODE_CONDITION_SOURCE => [
                    'method' => null
                ],
                PowerRoute::CONFIG_NODE_CONDITION_MATCHER => [
                    'isEqualTo' => 'POST'
                ]
            ],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    ['goTo' => 'expectationUrl']
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => [
                    ['goTo' => 'default']
                ]
            ]
        ],
        'expectationUrl' => [
            PowerRoute::CONFIG_NODE_CONDITION => [
                PowerRoute::CONFIG_NODE_CONDITION_SOURCE => [
                    'url' => 'path'
                ],
                PowerRoute::CONFIG_NODE_CONDITION_MATCHER => [
                    'matchesPattern' => '/\\_\\_expectation\/?$/'
                ]
            ],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    ['goTo' => 'jsonContent']
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => [
                    ['goTo' => 'default']
                ]
            ]
        ],
        'jsonContent' => [
            PowerRoute::CONFIG_NODE_CONDITION => [
                PowerRoute::CONFIG_NODE_CONDITION_SOURCE => [
                    'header' => 'Content-Type'
                ],
                PowerRoute::CONFIG_NODE_CONDITION_MATCHER => [
                    'matchesPattern' => '/application\/json/'
                ]
            ],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    [
                        'addExpectation' => [
                            'stubs' => $stubs,
                            'requestBuilder' => $requestBuilder
                        ]
                    ]
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => [
                    ['goTo' => 'default']
                ]
            ]
        ]
    ]
];
