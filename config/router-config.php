<?php
use Mcustiel\Phiremock\Server\Utils\Stubs;
use Mcustiel\SimpleRequest\RequestBuilder;

return [
    'start' => 'methodIsPost',
    'nodes' => [
        'expectationUrl' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [
                            'url' => 'path'
                        ],
                        'matcher' => [
                            'matchesPattern' => '/\\_\\_phiremock\/expectation\/?$/'
                        ],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['goTo' => 'expectationMethodIsPost']
                ],
                'else' => [
                    ['goTo' => 'default']
                ]
            ]
        ],
        'expectationMethodIsPost' => [
            'condition' => [
                'all-of' => [
                    [
                        'input-source' => [ 'method' => null ],
                        'matcher' => [ 'isEqualTo' => 'POST' ],
                    ],
                    [
                        'input-source' => [
                            'header' => 'Content-Type'
                        ],
                        'matcher' => [
                            'isEqualTo' => '/application\/json/'
                        ]
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [
                        'addExpectation' => null,
                    ],
                ],
                'else' => [
                    ['goTo' => 'expectationMethodIsGet'],
                ],
            ]
        ],
        'expectationMethodIsGet' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [ 'method' => null ],
                        'matcher' => [ 'isEqualTo' => 'GET' ],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['listExpectations' => null]
                ],
                'else' => [
                    ['goTo' => 'apiError']
                ]
            ]
        ],

        'apiError' => [
            'condition' => [],
            'actions' => [
                'if-matches' => [
                    ['serverError' => 'Invalid api request']
                ],
                'else' => []
            ]
        ],


        'default' => [
            'condition' => [],
            'actions' => [
                'if-matches' => [
                    ['parseExpectations' => null]
                ],
                'else' => [],
            ],
        ],
    ]
];
