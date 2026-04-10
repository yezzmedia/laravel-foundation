<?php

declare(strict_types=1);

use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\HttpMiddlewareRegistry;

it('stores http middleware definitions and supports lookup helpers', function (): void {
    $registry = new HttpMiddlewareRegistry;

    $registry->register(new HttpMiddlewareDefinition(
        key: 'ops.analytics.alias',
        package: 'yezzmedia/laravel-ops-analytics',
        middleware: 'App\\Http\\Middleware\\TrackAnalytics',
        kind: 'alias',
        alias: 'ops.analytics',
        description: 'Registers the analytics middleware alias.',
    ));

    $registry->register(new HttpMiddlewareDefinition(
        key: 'ops.analytics.web-prepend',
        package: 'yezzmedia/laravel-ops-analytics',
        middleware: 'App\\Http\\Middleware\\TrackAnalytics',
        kind: 'web_prepend',
        description: 'Prepends analytics tracking to the web group.',
    ));

    expect($registry->all())->toHaveCount(2)
        ->and($registry->has('ops.analytics.alias'))->toBeTrue()
        ->and($registry->forPackage('yezzmedia/laravel-ops-analytics')->pluck('key')->all())->toBe([
            'ops.analytics.alias',
            'ops.analytics.web-prepend',
        ])
        ->and($registry->forPackage('yezzmedia/laravel-missing'))->toHaveCount(0);
});

it('rejects duplicate http middleware definitions', function (): void {
    $registry = new HttpMiddlewareRegistry;
    $definition = new HttpMiddlewareDefinition(
        key: 'ops.analytics.alias',
        package: 'yezzmedia/laravel-ops-analytics',
        middleware: 'App\\Http\\Middleware\\TrackAnalytics',
        kind: 'alias',
        alias: 'ops.analytics',
        description: 'Registers the analytics middleware alias.',
    );

    $registry->register($definition);
    $registry->register($definition);
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty http middleware definition keys', function (): void {
    (new HttpMiddlewareRegistry)->register(new HttpMiddlewareDefinition(
        key: '',
        package: 'yezzmedia/laravel-ops-analytics',
        middleware: 'App\\Http\\Middleware\\TrackAnalytics',
        kind: 'alias',
        alias: 'ops.analytics',
        description: 'Registers the analytics middleware alias.',
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects registration after the http middleware registry is sealed', function (): void {
    $registry = new HttpMiddlewareRegistry;

    $registry->seal();

    $registry->register(new HttpMiddlewareDefinition(
        key: 'ops.analytics.alias',
        package: 'yezzmedia/laravel-ops-analytics',
        middleware: 'App\\Http\\Middleware\\TrackAnalytics',
        kind: 'alias',
        alias: 'ops.analytics',
        description: 'Registers the analytics middleware alias.',
    ));
})->throws(InvalidPackageDefinitionException::class, 'HTTP middleware registry is sealed.');
