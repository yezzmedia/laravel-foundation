<?php

declare(strict_types=1);

use Tests\Fixtures\FakeFeaturePackage;
use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\PackageManifestLoader;

it('stores explicitly registered packages in insertion order', function (): void {
    $loader = new PackageManifestLoader;

    $loader->register(new FakePlatformPackage(name: 'yezzmedia/laravel-settings'));
    $loader->register(new FakeFeaturePackage(name: 'yezzmedia/laravel-content'));

    expect(array_map(
        static fn (PlatformPackage $package): string => $package->metadata()->name,
        $loader->packages(),
    ))->toBe([
        'yezzmedia/laravel-settings',
        'yezzmedia/laravel-content',
    ]);
});

it('rejects registration after the package manifest loader is sealed', function (): void {
    $loader = new PackageManifestLoader;

    $loader->seal();
    $loader->register(new FakePlatformPackage);
})->throws(InvalidPackageDefinitionException::class, 'Package manifest loader is sealed.');
