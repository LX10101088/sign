<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'qrcode',
        ],
        'upgrade' => [
            'simditor',
        ],
        'config_init' => [
            'simditor',
        ],
    ],
    'route' => [
        '/qrcode$' => 'qrcode/index/index',
        '/qrcode/build$' => 'qrcode/index/build',
    ],
    'priority' => [],
    'domain' => '',
];
