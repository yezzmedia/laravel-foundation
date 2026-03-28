<?php

declare(strict_types=1);

use Tests\Fixtures\FakeFeaturePackage;
use YezzMedia\Foundation\Support\IntegrationManager;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

it('reports installed packages and supported features', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeFeaturePackage);

    $integrationManager = app(IntegrationManager::class);

    expect($integrationManager->installed('yezzmedia/laravel-content'))->toBeTrue()
        ->and($integrationManager->supports('content.pages'))->toBeTrue();
});

it('returns false for unknown packages and features', function (): void {
    $integrationManager = app(IntegrationManager::class);

    expect($integrationManager->installed('yezzmedia/laravel-missing'))->toBeFalse()
        ->and($integrationManager->supports('missing.feature'))->toBeFalse();
});
