<?php

declare(strict_types=1);

use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\CacheKeyFactory;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;
use YezzMedia\Foundation\Support\RateLimitKeyFactory;

it('registers the core foundation bindings', function (): void {
    expect(app(PackageRegistry::class))->toBeInstanceOf(PackageRegistry::class)
        ->and(app(FeatureRegistry::class))->toBeInstanceOf(FeatureRegistry::class)
        ->and(app(PermissionRegistry::class))->toBeInstanceOf(PermissionRegistry::class)
        ->and(app(OpsModuleRegistry::class))->toBeInstanceOf(OpsModuleRegistry::class)
        ->and(app(PlatformPackageRegistrar::class))->toBeInstanceOf(PlatformPackageRegistrar::class)
        ->and(app(InstallManager::class))->toBeInstanceOf(InstallManager::class)
        ->and(app(DoctorManager::class))->toBeInstanceOf(DoctorManager::class)
        ->and(app(CacheKeyFactory::class))->toBeInstanceOf(CacheKeyFactory::class)
        ->and(app(RateLimitKeyFactory::class))->toBeInstanceOf(RateLimitKeyFactory::class);
});

it('merges the package configuration', function (): void {
    expect(config('foundation.vendor'))->toBe('yezzmedia')
        ->and(config('foundation.cache.prefix'))->toBe('website')
        ->and(config('foundation.install.command'))->toBe('website:install');
});
