<?php

declare(strict_types=1);

use Tests\Fixtures\FakeCapabilityPackage;
use Tests\Fixtures\FakeDoctorCheck;
use Tests\Fixtures\FakeDoctorPackage;
use Tests\Fixtures\FakeFeaturePackage;
use Tests\Fixtures\FakeInstallPackage;
use Tests\Fixtures\FakeInstallStep;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\IntegrationManager;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    FakeDoctorCheck::reset();
    FakeInstallStep::reset();
});

it('keeps multi package state consistent across registries and integration helpers', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage);
    $registrar->register(new FakeCapabilityPackage(
        permissions: [
            new PermissionDefinition('ops.audit.view', 'yezzmedia/laravel-ops', 'View audit logs'),
        ],
        opsModules: [
            new OpsModuleDefinition('ops.audit', 'yezzmedia/laravel-ops', 'Audit trail', 'page'),
        ],
    ));

    $integrationManager = app(IntegrationManager::class);

    expect(app(PackageRegistry::class)->all()->pluck('name')->all())->toBe([
        'yezzmedia/laravel-content',
        'yezzmedia/laravel-ops',
    ])
        ->and(app(FeatureRegistry::class)->has('content.pages'))->toBeTrue()
        ->and(app(PermissionRegistry::class)->all()->pluck('name')->all())->toBe([
            'ops.audit.view',
        ])
        ->and(app(OpsModuleRegistry::class)->all()->pluck('key')->all())->toBe([
            'ops.audit',
        ])
        ->and($integrationManager->installed('yezzmedia/laravel-content'))->toBeTrue()
        ->and($integrationManager->supports('content.pages'))->toBeTrue();
});

it('runs smoke commands against a combined package state', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage);
    $registrar->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));
    $registrar->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('config', 'yezzmedia/laravel-health', status: 'warning', message: 'Queue worker is not running.')],
    ));

    $packagesCommand = artisan('website:packages');

    if (is_int($packagesCommand)) {
        throw new RuntimeException('Expected pending command for website:packages.');
    }

    $packagesCommand
        ->assertSuccessful();

    $featuresCommand = artisan('website:features');

    if (is_int($featuresCommand)) {
        throw new RuntimeException('Expected pending command for website:features.');
    }

    $featuresCommand
        ->assertSuccessful();

    $installCommand = artisan('website:install');

    if (is_int($installCommand)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $installCommand
        ->assertSuccessful();

    $doctorCommand = artisan('website:doctor');

    if (is_int($doctorCommand)) {
        throw new RuntimeException('Expected pending command for website:doctor.');
    }

    $doctorCommand
        ->assertSuccessful();
});
