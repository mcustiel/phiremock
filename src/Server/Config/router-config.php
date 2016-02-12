<?php

return [
    'start' => 'expectationUrl',
    'nodes' => [

// -------------------------------- API: expectations ---------------------------
        'expectationUrl' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => ['url' => 'path'],
                        'matcher' => [
                            'matches' => '/\\_\\_phiremock\/expectation\/?$/'
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
                        'input-source' => ['method' => null],
                        'matcher' => ['isEqualTo' => 'POST'],
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
                        'input-source' => ['method' => null],
                        'matcher' => ['isEqualTo' => 'GET'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['listExpectations' => null],
                ],
                'else' => [
                    ['goto' => 'expectationMethodIsDelete'],
                ],
            ],
        ],
        'expectationMethodIsDelete' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => ['method' => null],
                        'matcher' => ['isEqualTo' => 'DELETE'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['clearExpectations' => null],
                ],
                'else' => [
                    ['goto' => 'apiError'],
                ],
            ],
        ],

// -------------------------------- API: scenarios ---------------------------
        'scenariosUrl' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => ['url' => 'path'],
                        'matcher' => [
                            'matches' => '/\\_\\_phiremock\/scenarios\/?$/'
                        ],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['scenariosMethodIsDelete' => null],
                ],
                'else' => [
                    ['goto' => 'default'],
                ],
            ],
        ],
        'scenariosMethodIsDelete' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => ['method' => null],
                        'matcher' => ['isEqualTo' => 'DELETE'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['clearScenarios' => null],
                ],
                'else' => [
                    ['goto' => 'apiError'],
                ],
            ],
        ],

// -------------------------------- API: error happened ---------------------------
        'apiError' => [
            'condition' => [],
            'actions' => [
                'if-matches' => [
                    ['serverError' => null],
                ],
                'else' => [],
            ],
        ],

// ---------------------------- Verify configured expectations -----------------------
        'default' => [
            'condition' => [],
            'actions' => [
                'if-matches' => [
                    ['checkExpectations' => null],
                    ['verifyExpectations' => null],
                ],
                'else' => [],
            ],
        ],
    ],
];
