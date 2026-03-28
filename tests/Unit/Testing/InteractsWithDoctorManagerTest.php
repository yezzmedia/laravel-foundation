<?php

declare(strict_types=1);

use Tests\Fixtures\FakeDoctorCheck;
use Tests\Fixtures\FakeDoctorPackage;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithDoctorManager;
use YezzMedia\Foundation\Testing\Concerns\InteractsWithPackageRegistry;

beforeEach(function (): void {
    FakeDoctorCheck::reset();
});

it('runs doctor checks through the doctor manager helpers', function (): void {
    $testHelper = new class
    {
        use InteractsWithDoctorManager;
        use InteractsWithPackageRegistry;
    };

    $testHelper->registerPackage(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('config', 'yezzmedia/laravel-health', status: 'warning')],
    ));

    $results = $testHelper->runDoctor();

    $testHelper->assertDoctorResult($results, 'config', 'warning');
});

it('returns blocking doctor failures through the helper', function (): void {
    $testHelper = new class
    {
        use InteractsWithDoctorManager;
        use InteractsWithPackageRegistry;
    };

    $testHelper->registerPackage(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('config', 'yezzmedia/laravel-health', status: 'failed', isBlocking: true)],
    ));

    expect($testHelper->failingDoctorResults()->pluck('key')->all())->toBe([
        'config',
    ]);
});
