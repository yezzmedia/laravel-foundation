<?php

declare(strict_types=1);

use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Router;
use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Registry\HttpMiddlewareRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Support\FoundationHttpMiddlewareBridge;
use YezzMedia\Foundation\Support\HttpMiddlewareResolver;

function foundationHttpMiddlewareBridge(array $definitions): FoundationHttpMiddlewareBridge
{
    $registry = new HttpMiddlewareRegistry;
    $packages = new PackageRegistry;

    $packages->register((new FakePlatformPackage('yezzmedia/laravel-ops-analytics', true, 10))->metadata());

    foreach ($definitions as $definition) {
        $registry->register($definition);
    }

    return new FoundationHttpMiddlewareBridge(new HttpMiddlewareResolver($registry, $packages));
}

it('applies aliases and web middleware to the middleware configuration once', function (): void {
    $bridge = foundationHttpMiddlewareBridge([
        new HttpMiddlewareDefinition(
            key: 'ops.analytics.alias',
            package: 'yezzmedia/laravel-ops-analytics',
            middleware: 'App\\Http\\Middleware\\TrackAnalytics',
            kind: 'alias',
            alias: 'ops.analytics',
            description: 'Analytics alias.',
        ),
        new HttpMiddlewareDefinition(
            key: 'ops.analytics.prepend',
            package: 'yezzmedia/laravel-ops-analytics',
            middleware: 'App\\Http\\Middleware\\ResolveConsent',
            kind: 'web_prepend',
            description: 'Consent resolver.',
        ),
        new HttpMiddlewareDefinition(
            key: 'ops.analytics.append',
            package: 'yezzmedia/laravel-ops-analytics',
            middleware: 'App\\Http\\Middleware\\TrackAnalytics',
            kind: 'web_append',
            description: 'Analytics tracking.',
        ),
    ]);

    $middleware = new Middleware;

    $bridge->applyToConfiguration($middleware);
    $bridge->applyToConfiguration($middleware);

    $aliases = $middleware->getMiddlewareAliases();
    $webGroup = $middleware->getMiddlewareGroups()['web'];

    expect($aliases['ops.analytics'])->toBe('App\\Http\\Middleware\\TrackAnalytics')
        ->and(array_values(array_filter($webGroup, static fn (string $middlewareClass): bool => $middlewareClass === 'App\\Http\\Middleware\\ResolveConsent')))->toHaveCount(1)
        ->and(array_values(array_filter($webGroup, static fn (string $middlewareClass): bool => $middlewareClass === 'App\\Http\\Middleware\\TrackAnalytics')))->toHaveCount(1)
        ->and(array_search('App\\Http\\Middleware\\ResolveConsent', $webGroup, true))->toBe(0)
        ->and(array_search('App\\Http\\Middleware\\TrackAnalytics', $webGroup, true))->toBe(count($webGroup) - 1);
});

it('applies aliases and web middleware to the router once', function (): void {
    $bridge = foundationHttpMiddlewareBridge([
        new HttpMiddlewareDefinition(
            key: 'ops.analytics.alias',
            package: 'yezzmedia/laravel-ops-analytics',
            middleware: 'App\\Http\\Middleware\\TrackAnalytics',
            kind: 'alias',
            alias: 'ops.analytics',
            description: 'Analytics alias.',
        ),
        new HttpMiddlewareDefinition(
            key: 'ops.analytics.prepend',
            package: 'yezzmedia/laravel-ops-analytics',
            middleware: 'App\\Http\\Middleware\\ResolveConsent',
            kind: 'web_prepend',
            description: 'Consent resolver.',
        ),
        new HttpMiddlewareDefinition(
            key: 'ops.analytics.append',
            package: 'yezzmedia/laravel-ops-analytics',
            middleware: 'App\\Http\\Middleware\\TrackAnalytics',
            kind: 'web_append',
            description: 'Analytics tracking.',
        ),
    ]);

    /** @var Router $router */
    $router = app(Router::class);

    $bridge->applyToRouter($router);
    $bridge->applyToRouter($router);

    $aliases = $router->getMiddleware();
    $webGroup = $router->getMiddlewareGroups()['web'];

    expect($aliases['ops.analytics'])->toBe('App\\Http\\Middleware\\TrackAnalytics')
        ->and(array_values(array_filter($webGroup, static fn (string $middlewareClass): bool => $middlewareClass === 'App\\Http\\Middleware\\ResolveConsent')))->toHaveCount(1)
        ->and(array_values(array_filter($webGroup, static fn (string $middlewareClass): bool => $middlewareClass === 'App\\Http\\Middleware\\TrackAnalytics')))->toHaveCount(1)
        ->and(array_search('App\\Http\\Middleware\\ResolveConsent', $webGroup, true))->toBe(0)
        ->and(array_search('App\\Http\\Middleware\\TrackAnalytics', $webGroup, true))->toBe(count($webGroup) - 1);
});
