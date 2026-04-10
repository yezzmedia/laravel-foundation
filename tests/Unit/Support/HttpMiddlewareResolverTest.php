<?php

declare(strict_types=1);

use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Registry\HttpMiddlewareRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Support\HttpMiddlewareResolver;

it('resolves aliases and web middleware in package priority order', function (): void {
    $definitions = new HttpMiddlewareRegistry;
    $packages = new PackageRegistry;

    $packages->register((new FakePlatformPackage('yezzmedia/laravel-low', true, 20))->metadata());
    $packages->register((new FakePlatformPackage('yezzmedia/laravel-high', true, 5))->metadata());

    $definitions->register(new HttpMiddlewareDefinition(
        key: 'low.alias',
        package: 'yezzmedia/laravel-low',
        middleware: 'App\\Http\\Middleware\\LowAlias',
        kind: 'alias',
        alias: 'low.alias',
        description: 'Low priority alias.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'high.alias',
        package: 'yezzmedia/laravel-high',
        middleware: 'App\\Http\\Middleware\\HighAlias',
        kind: 'alias',
        alias: 'high.alias',
        description: 'High priority alias.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'low.prepend',
        package: 'yezzmedia/laravel-low',
        middleware: 'App\\Http\\Middleware\\TrackRequests',
        kind: 'web_prepend',
        description: 'Low priority prepend.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'high.prepend',
        package: 'yezzmedia/laravel-high',
        middleware: 'App\\Http\\Middleware\\ResolveTenant',
        kind: 'web_prepend',
        description: 'High priority prepend.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'low.append',
        package: 'yezzmedia/laravel-low',
        middleware: 'App\\Http\\Middleware\\TrackRequests',
        kind: 'web_append',
        description: 'Low priority append.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'high.append',
        package: 'yezzmedia/laravel-high',
        middleware: 'App\\Http\\Middleware\\ShareContext',
        kind: 'web_append',
        description: 'High priority append.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'disabled.alias',
        package: 'yezzmedia/laravel-high',
        middleware: 'App\\Http\\Middleware\\DisabledAlias',
        kind: 'alias',
        alias: 'disabled.alias',
        enabled: false,
        description: 'Disabled alias.',
    ));

    $resolved = (new HttpMiddlewareResolver($definitions, $packages))->resolve();

    expect(array_keys($resolved['aliases']))->toBe(['high.alias', 'low.alias'])
        ->and($resolved['aliases'])->toBe([
            'high.alias' => 'App\\Http\\Middleware\\HighAlias',
            'low.alias' => 'App\\Http\\Middleware\\LowAlias',
        ])
        ->and($resolved['web_prepend'])->toBe([
            'App\\Http\\Middleware\\ResolveTenant',
            'App\\Http\\Middleware\\TrackRequests',
        ])
        ->and($resolved['web_append'])->toBe([
            'App\\Http\\Middleware\\ShareContext',
            'App\\Http\\Middleware\\TrackRequests',
        ]);
});

it('falls back to package name and key ordering when priorities match', function (): void {
    $definitions = new HttpMiddlewareRegistry;
    $packages = new PackageRegistry;

    $packages->register((new FakePlatformPackage('yezzmedia/laravel-beta', true, 10))->metadata());
    $packages->register((new FakePlatformPackage('yezzmedia/laravel-alpha', true, 10))->metadata());

    $definitions->register(new HttpMiddlewareDefinition(
        key: 'beta.prepend',
        package: 'yezzmedia/laravel-beta',
        middleware: 'App\\Http\\Middleware\\Beta',
        kind: 'web_prepend',
        description: 'Beta prepend.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'alpha.zed',
        package: 'yezzmedia/laravel-alpha',
        middleware: 'App\\Http\\Middleware\\AlphaZed',
        kind: 'web_prepend',
        description: 'Alpha zed prepend.',
    ));
    $definitions->register(new HttpMiddlewareDefinition(
        key: 'alpha.able',
        package: 'yezzmedia/laravel-alpha',
        middleware: 'App\\Http\\Middleware\\AlphaAble',
        kind: 'web_prepend',
        description: 'Alpha able prepend.',
    ));

    $resolved = (new HttpMiddlewareResolver($definitions, $packages))->resolve();

    expect($resolved['web_prepend'])->toBe([
        'App\\Http\\Middleware\\AlphaAble',
        'App\\Http\\Middleware\\AlphaZed',
        'App\\Http\\Middleware\\Beta',
    ]);
});
