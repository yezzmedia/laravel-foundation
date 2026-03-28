<?php

declare(strict_types=1);

use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\CacheKeyFactory;

it('builds namespaced cache keys with optional segments', function (): void {
    $key = (new CacheKeyFactory)->make('navigation', 'tree', 'main', ['de', 'public']);

    expect($key)->toBe('website:navigation:tree:main:de:public');
});

it('trims cache key values and supports numeric segments', function (): void {
    $key = (new CacheKeyFactory)->make(' navigation ', ' tree ', ' main ', [' de ', 42]);

    expect($key)->toBe('website:navigation:tree:main:de:42');
});

it('rejects empty cache key parts', function (): void {
    (new CacheKeyFactory)->make('navigation', '', 'main');
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty optional cache segments', function (): void {
    (new CacheKeyFactory)->make('navigation', 'tree', 'main', ['de', '   ']);
})->throws(InvalidPackageDefinitionException::class);
