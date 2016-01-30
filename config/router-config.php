<?php

return [
    'start' => 'expectationUrl',
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
                    ['goto' => 'expectationMethodIsPost'],
                ],
                'else' => [
                    ['goto' => 'default'],
                ],
            ],
        ],
        'expectationMethodIsPost' => [
            'condition' => [
                'all-of' => [
                    [
                        'input-source' => [ 'method' => null ],
                        'matcher' => [ 'isEqualTo' => 'POST' ],
                    ],
                    [
                        'input-source' => ['header' => 'Content-Type'],
                        'matcher' => ['isEqualTo' => 'application/json'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['addExpectation' => null],
                ],
                'else' => [
                    ['goto' => 'expectationMethodIsGet'],
                ],
            ],
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
                    ['listExpectations' => null],
                ],
                'else' => [
                    ['goto' => 'apiError'],
                ],
            ],
        ],

        'apiError' => [
            'condition' => [],
            'actions' => [
                'if-matches' => [
                    ['serverError' => null],
                ],
                'else' => [],
            ],
        ],


        'default' => [
            'condition' => [],
            'actions' => [
                'if-matches' => [
                    ['parseExpectations' => null],
                ],
                'else' => [],
            ],
        ],
    ],
];
