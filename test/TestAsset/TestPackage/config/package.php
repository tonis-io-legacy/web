<?php
use Tonis\View\Strategy\StringStrategy;

return [
    'foo' => 'bar',
    'mvc' => [
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
