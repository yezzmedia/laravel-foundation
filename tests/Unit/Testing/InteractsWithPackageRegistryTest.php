<?php

declare(strict_types=1);

use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithPackageRegistry;

it('registers and exposes packages through the package registry helpers', function (): void {
    $testHelper = new class
    {
        use InteractsWithPackageRegistry;
    };

    $package = $testHelper->registerPackage(new FakePlatformPackage);

    $testHelper->assertRegisteredPackage($package->metadata()->name);

    expect($testHelper->registeredPackages()->pluck('name')->all())->toBe([
        'yezzmedia/laravel-foundation',
        'yezzmedia/laravel-settings',
    ]);
});
