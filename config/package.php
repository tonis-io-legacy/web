<?php
use Tonis\View\Strategy\JsonStrategy;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\StringStrategy;

return [
    'required_environment' => [
        'TONIS_DEBUG'
    ],
    'plates' => [
        'folders' => [
            'error' => __DIR__ . '/../view/plates/error',
            'layout' => __DIR__ . '/../view/plates/layout'
        ]
    ],
    'twig' => [
        'options' => [
            'cache' => getenv('TONIS_DEBUG') ? null : 'cache/twig'
        ],
        'extensions' => [],
        'namespaces' => [
            'error' => __DIR__ . '/../view/twig/error',
            'layout' => __DIR__ . '/../view/twig/layout'
        ]
    ],
    'view_manager' => [
        'not_found_template' => '@error/404',
        'error_template' => '@error/error',
        'strategies' => [
            StringStrategy::class,
            JsonStrategy::class,
            PlatesStrategy::class
        ],
    ]
];
