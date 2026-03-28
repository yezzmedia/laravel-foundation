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

it('escapes separator characters in cache key segments', function (): void {
    $key = (new CacheKeyFactory)->make('nav:core', 'tree%main', 'left:rail', ['de:public']);

    expect($key)->toBe('website:nav%3Acore:tree%25main:left%3Arail:de%3Apublic');
});

it('uses configured prefixes and separators when provided', function (): void {
    $key = (new CacheKeyFactory(prefix: 'foundation', separator: '|'))->make('navigation', 'tree', 'main', ['de|public']);

    expect($key)->toBe('foundation|navigation|tree|main|de%7Cpublic');
});

it('rejects empty cache key parts', function (): void {
    (new CacheKeyFactory)->make('navigation', '', 'main');
})->throws(InvalidPackageDefinitionException::class);

it('rejects empty optional cache segments', function (): void {
    (new CacheKeyFactory)->make('navigation', 'tree', 'main', ['de', '   ']);
})->throws(InvalidPackageDefinitionException::class);
