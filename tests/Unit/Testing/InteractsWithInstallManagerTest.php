<?php

declare(strict_types=1);

use Tests\Fixtures\FakeInstallPackage;
use Tests\Fixtures\FakeInstallStep;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithInstallManager;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithPackageRegistry;

it('runs installs through the install manager helpers', function (): void {
    $testHelper = new class
    {
        use InteractsWithInstallManager;
        use InteractsWithPackageRegistry;
    };

    $testHelper->registerPackage(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));

    $result = $testHelper->runInstall();

    $testHelper->assertInstallStatus($result, 'success');
    $testHelper->assertExecutedInstallStep($result, 'yezzmedia/laravel-install', 'bootstrap');
});
