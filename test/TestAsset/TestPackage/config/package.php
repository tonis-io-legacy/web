<?php
use Tonis\Mvc\TestAsset\TestTwigExtension;
use Tonis\View\Strategy\StringStrategy;

return [
    'foo' => 'bar',
    'mvc' => [
        'plates' => [
            'folders' => [
                'foo' => __DIR__
            ]
        ],
        'twig' => [
            'extensions' => [
                TestTwigExtension::class
            ],
            'options' => [],
            'namespaces' => [
                'foo' => __DIR__
            ]
        ],
        'view_manager' => [
            'strategies' => [
                StringStrategy::class,
                'foo' => null
            ],
            'error_template' => '@error/error',
            'not_found_template' => '@error/404'
        ]
    ]
];
