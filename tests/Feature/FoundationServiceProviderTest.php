<?php

declare(strict_types=1);

use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

it('registers the core foundation bindings', function (): void {
    expect(app(PackageRegistry::class))->toBeInstanceOf(PackageRegistry::class)
        ->and(app(FeatureRegistry::class))->toBeInstanceOf(FeatureRegistry::class)
        ->and(app(PlatformPackageRegistrar::class))->toBeInstanceOf(PlatformPackageRegistrar::class);
});

it('merges the package configuration', function (): void {
    expect(config('foundation.vendor'))->toBe('yezzmedia')
        ->and(config('foundation.cache.prefix'))->toBe('website')
        ->and(config('foundation.install.command'))->toBe('website:install');
});
