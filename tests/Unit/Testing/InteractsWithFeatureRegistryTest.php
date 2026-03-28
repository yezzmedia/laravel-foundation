<?php

declare(strict_types=1);

use Tests\Fixtures\FakeFeaturePackage;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithFeatureRegistry;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithPackageRegistry;

it('exposes registered features through the feature registry helpers', function (): void {
    $testHelper = new class
    {
        use InteractsWithFeatureRegistry;
        use InteractsWithPackageRegistry;
    };

    $testHelper->registerPackage(new FakeFeaturePackage);

    $testHelper->assertRegisteredFeature('content.pages');

    expect($testHelper->registeredFeatures()->pluck('name')->all())->toBe([
        'content.pages',
    ]);
});
