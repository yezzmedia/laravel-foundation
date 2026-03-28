<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Tests\Fixtures\FakeFeaturePackage;
use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Events\FeatureRegistered;
use YezzMedia\Foundation\Events\PackageRegistered;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

it('registers package metadata and features through the registrar', function (): void {
    Event::fake([PackageRegistered::class, FeatureRegistered::class]);

    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage);

    expect(app(PackageRegistry::class)->has('yezzmedia/laravel-content'))->toBeTrue()
        ->and(app(FeatureRegistry::class)->has('content.pages'))->toBeTrue();

    Event::assertDispatched(PackageRegistered::class);
    Event::assertDispatched(FeatureRegistered::class);
});

it('does not register features for disabled packages', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage(enabled: false));

    expect(app(PackageRegistry::class)->has('yezzmedia/laravel-content'))->toBeTrue()
        ->and(app(FeatureRegistry::class)->has('content.pages'))->toBeFalse();
});

it('rejects duplicate package registration', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);
    $package = new FakePlatformPackage;

    $registrar->register($package);
    $registrar->register($package);
})->throws(InvalidPackageDefinitionException::class);

it('rejects feature definitions that belong to another package', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage(featurePackage: 'yezzmedia/laravel-other'));
})->throws(InvalidPackageDefinitionException::class);
