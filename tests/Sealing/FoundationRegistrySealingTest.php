<?php

declare(strict_types=1);

use Tests\Fixtures\FakePlatformPackage;
use Tests\SealedFoundationTestCase;
use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\HttpMiddlewareRegistry;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

uses(SealedFoundationTestCase::class);

it('rejects package registration after the foundation state has been sealed', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakePlatformPackage);
})->throws(InvalidPackageDefinitionException::class, 'Package registry is sealed.');

it('seals the http middleware registry after boot in sealed runtime', function (): void {
    app(HttpMiddlewareRegistry::class)->register(new HttpMiddlewareDefinition(
        key: 'test.alias',
        package: 'yezzmedia/laravel-foundation',
        middleware: 'Tests\\Fixtures\\Middleware\\ExampleMiddleware',
        kind: 'alias',
        alias: 'test.alias',
        description: 'A sealed runtime test middleware alias.',
    ));
})->throws(InvalidPackageDefinitionException::class, 'HTTP middleware registry is sealed.');
