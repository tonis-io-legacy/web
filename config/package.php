<?php
return [
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
    'mvc' => [
        'subscribers' => [],
        'view_manager' => [
            'not_found_template' => '@error/404',
            'error_template' => '@error/error',
            'strategies' => [],
        ]
    ]
];
