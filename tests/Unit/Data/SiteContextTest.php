<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\SiteContext;

it('stores the approved site context fields', function (): void {
    $context = new SiteContext(
        environment: 'testing',
        locale: 'en',
    );

    expect($context->environment)->toBe('testing')
        ->and($context->locale)->toBe('en');
});
