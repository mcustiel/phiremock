<?php
use Mcustiel\PowerRoute\PowerRoute;
use Mcustiel\Phiremock\Server\Utils\Stubs;
use Mcustiel\SimpleRequest\RequestBuilder;

$stubs = Stubs();
$requestBuilder = new RequestBuilder();

return [
    PowerRoute::CONFIG_ROOT_NODE => 'methodIsPost',
    PowerRoute::CONFIG_NODES => [
        'expectationUrl' => [
            PowerRoute::CONFIG_NODE_CONDITION => [
                PowerRoute::CONFIG_NODE_CONDITION_SOURCE => [
                    'url' => 'path'
                ],
                PowerRoute::CONFIG_NODE_CONDITION_MATCHER => [
                    'matchesPattern' => '/\\_\\_phiremock\/expectation\/?$/'
                ]
            ],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    ['goTo' => 'expectationMethodIsPost']
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => [
                    ['goTo' => 'default']
                ]
            ]
        ],
        'expectationMethodIsPost' => [
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
                    ['addExpectation' => [
                            'stubs' => $stubs,
                            'requestBuilder' => $requestBuilder
                        ]
                    ]
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => [
                    ['goTo' => 'expectationMethodIsGet']
                ]
            ]
        ],
        'expectationMethodIsGet' => [
            PowerRoute::CONFIG_NODE_CONDITION => [
                PowerRoute::CONFIG_NODE_CONDITION_SOURCE => [
                    'method' => null
                ],
                PowerRoute::CONFIG_NODE_CONDITION_MATCHER => [
                    'isEqualTo' => 'GET'
                ]
            ],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    ['listExpectations' => ['stubs' => $stubs]]
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => [
                    ['goTo' => 'apiError']
                ]
            ]
        ],

        'apiError' => [
            PowerRoute::CONFIG_NODE_CONDITION => [],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    ['serverError' => 'Invalid api request']
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => []
            ]
        ],


        'default' => [
            PowerRoute::CONFIG_NODE_CONDITION => [],
            PowerRoute::CONFIG_NODE_CONDITION_ACTIONS => [
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_MATCH => [
                    ['parseExpectations' => null]
                ],
                PowerRoute::CONFIG_NODE_CONDITION_ACTIONS_NOTMATCH => []
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
