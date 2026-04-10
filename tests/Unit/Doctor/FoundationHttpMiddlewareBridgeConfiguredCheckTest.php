<?php

declare(strict_types=1);

use YezzMedia\Foundation\Doctor\FoundationHttpMiddlewareBridgeConfiguredCheck;

it('reports skipped when the host bootstrap file is unavailable', function (): void {
    $check = new FoundationHttpMiddlewareBridgeConfiguredCheck(sys_get_temp_dir().'/foundation-bridge-missing-'.uniqid().'.php');

    $result = $check->run();

    expect($result->status)->toBe('skipped')
        ->and($result->message)->toContain('could not be verified')
        ->and($result->context['configured'])->toBeFalse();
});

it('reports warning when the host bootstrap file does not configure the bridge', function (): void {
    $path = sys_get_temp_dir().'/foundation-bridge-warning-'.uniqid().'.php';

    file_put_contents($path, <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->create();
PHP);

    try {
        $result = (new FoundationHttpMiddlewareBridgeConfiguredCheck($path))->run();

        expect($result->status)->toBe('warning')
            ->and($result->message)->toContain('is not configured')
            ->and($result->context['configured'])->toBeFalse();
    } finally {
        unlink($path);
    }
});

it('reports passed when the host bootstrap file configures the bridge', function (): void {
    $path = sys_get_temp_dir().'/foundation-bridge-passed-'.uniqid().'.php';

    file_put_contents($path, <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        \YezzMedia\Foundation\Support\FoundationHttpMiddlewareBridge::apply($middleware);
    })
    ->create();
PHP);

    try {
        $result = (new FoundationHttpMiddlewareBridgeConfiguredCheck($path))->run();

        expect($result->status)->toBe('passed')
            ->and($result->message)->toContain('is configured')
            ->and($result->context['configured'])->toBeTrue();
    } finally {
        unlink($path);
    }
});
