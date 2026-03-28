<?php

declare(strict_types=1);

return [
    'vendor' => 'yezzmedia',
    'namespace' => 'website',

    'registry' => [
        'seal_after_boot' => true,
    ],

    'install' => [
        'command' => 'website:install',
    ],

    'doctor' => [
        'command' => 'website:doctor',
    ],

    'rate_limits' => [
        'separator' => '.',
    ],

    'cache' => [
        'prefix' => 'website',
        'separator' => ':',
    ],
];
