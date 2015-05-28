<?php
return [
    'tonis' => [
        'required_environment' => [
            'TONIS_DEBUG'
        ],
        'twig' => [
            'options' => [
                'cache' => getenv('TONIS_DEBUG') ? null : 'cache/twig'
            ],
            'extensions' => []
        ],
        'view_manager' => [
            'fallback_strategy' => 'Tonis\View\Twig\TwigStrategy',
            'not_found_template' => 'error/404',
            'error_template' => 'error/error',
            'strategies' => [],
        ]
    ]
];
