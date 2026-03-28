<?php

declare(strict_types=1);

use YezzMedia\Foundation\Contracts\ResolvesSiteContext;
use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Data\SiteContext;
use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\CacheKeyFactory;
use YezzMedia\Foundation\Support\IntegrationManager;
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
        ->and(app(IntegrationManager::class))->toBeInstanceOf(IntegrationManager::class)
        ->and(app(ResolvesSiteContext::class)->resolve())->toBeInstanceOf(SiteContext::class)
        ->and(app(CacheKeyFactory::class))->toBeInstanceOf(CacheKeyFactory::class)
        ->and(app(RateLimitKeyFactory::class))->toBeInstanceOf(RateLimitKeyFactory::class);
});

it('merges the package configuration', function (): void {
    expect(config('foundation.vendor'))->toBe('yezzmedia')
        ->and(config('foundation.cache.prefix'))->toBe('website')
        ->and(config('foundation.install.command'))->toBe('website:install');
});

it('uses configured separators and prefixes in resolved key factories', function (): void {
    config()->set('foundation.cache.prefix', 'foundation');
    config()->set('foundation.cache.separator', '|');
    config()->set('foundation.rate_limits.separator', '|');

    $cacheKey = app(CacheKeyFactory::class)->make('navigation', 'tree', 'main', ['de|public']);
    $rateLimitKey = app(RateLimitKeyFactory::class)->make(new RateLimitDefinition(
        key: 'ops.login',
        package: 'yezzmedia/laravel-ops',
        description: 'Protect admin logins.',
        maxAttempts: 5,
        decaySeconds: 60,
        scope: 'ip',
        keyStrategy: 'ip-only',
    ), [
        'ip' => '203.0.113.42|edge',
    ]);

    expect($cacheKey)->toBe('foundation|navigation|tree|main|de%7Cpublic')
        ->and($rateLimitKey)->toBe('ops.login|ip|203.0.113.42%7Cedge');
});
