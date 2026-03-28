<?php

declare(strict_types=1);

use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Testing\FoundationTestCase;

it('provides predictable config state and foundation bindings', function (): void {
    expect($this)->toBeInstanceOf(FoundationTestCase::class)
        ->and(config('cache.default'))->toBe('array')
        ->and(config('session.driver'))->toBe('array')
        ->and(config('queue.default'))->toBe('sync')
        ->and(app(PackageRegistry::class))->toBeInstanceOf(PackageRegistry::class)
        ->and(app(FeatureRegistry::class))->toBeInstanceOf(FeatureRegistry::class)
        ->and(app(InstallManager::class))->toBeInstanceOf(InstallManager::class)
        ->and(app(DoctorManager::class))->toBeInstanceOf(DoctorManager::class);
});
