<?php

declare(strict_types=1);

return [
    'registry' => [
        'seal_after_boot' => true,
    ],

    'rate_limits' => [
        'separator' => ':',
    ],

    'cache' => [
        'prefix' => 'website',
        'separator' => ':',
    ],
];
