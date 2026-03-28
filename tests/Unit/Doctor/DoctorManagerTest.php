<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\FakeDoctorCheck;
use Tests\Fixtures\FakeDoctorPackage;
use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Events\DoctorChecksCompleted;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

beforeEach(function (): void {
    FakeDoctorCheck::reset();
});

it('runs doctor checks in deterministic order and dispatches a completion summary', function (): void {
    Event::fake([DoctorChecksCompleted::class]);

    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeDoctorPackage(
        name: 'yezzmedia/laravel-zeta',
        checks: [
            new FakeDoctorCheck('z-last', 'yezzmedia/laravel-zeta', status: 'warning'),
            new FakeDoctorCheck('a-first', 'yezzmedia/laravel-zeta', status: 'skipped'),
        ],
    ));

    $registrar->register(new FakeDoctorPackage(
        name: 'yezzmedia/laravel-alpha',
        checks: [
            new FakeDoctorCheck('middle', 'yezzmedia/laravel-alpha', status: 'failed', isBlocking: true),
            new FakeDoctorCheck('first', 'yezzmedia/laravel-alpha', status: 'passed'),
        ],
    ));

    $results = app(DoctorManager::class)->run();

    expect($results->pluck('key')->all())->toBe([
        'first',
        'middle',
        'a-first',
        'z-last',
    ])
        ->and(FakeDoctorCheck::executed())->toBe([
            'yezzmedia/laravel-alpha:first',
            'yezzmedia/laravel-alpha:middle',
            'yezzmedia/laravel-zeta:a-first',
            'yezzmedia/laravel-zeta:z-last',
        ]);

    Event::assertDispatched(DoctorChecksCompleted::class, function (DoctorChecksCompleted $event): bool {
        return $event->summary === [
            'passed' => 1,
            'warning' => 1,
            'failed' => 1,
            'skipped' => 1,
        ];
    });
});

it('summarizes warning and skipped results without failures', function (): void {
    Event::fake([DoctorChecksCompleted::class]);

    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [
            new FakeDoctorCheck('warning-check', 'yezzmedia/laravel-health', status: 'warning'),
            new FakeDoctorCheck('skipped-check', 'yezzmedia/laravel-health', status: 'skipped'),
        ],
    ));

    $results = app(DoctorManager::class)->run();

    expect($results->pluck('status')->all())->toBe(['skipped', 'warning']);

    Event::assertDispatched(DoctorChecksCompleted::class, function (DoctorChecksCompleted $event): bool {
        return $event->summary === [
            'passed' => 0,
            'warning' => 1,
            'failed' => 0,
            'skipped' => 1,
        ];
    });
});

it('dispatches an empty completion summary when no doctor checks are registered', function (): void {
    Event::fake([DoctorChecksCompleted::class]);

    $results = app(DoctorManager::class)->run();

    expect($results)->toHaveCount(0);

    Event::assertDispatched(DoctorChecksCompleted::class, function (DoctorChecksCompleted $event): bool {
        return $event->summary === [
            'passed' => 0,
            'warning' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
    });
});

it('returns only blocking failed results from failing', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeDoctorPackage(
        checks: [
            new FakeDoctorCheck('passed-check', 'yezzmedia/laravel-health', status: 'passed'),
            new FakeDoctorCheck('non-blocking-failure', 'yezzmedia/laravel-health', status: 'failed', isBlocking: false),
            new FakeDoctorCheck('blocking-failure', 'yezzmedia/laravel-health', status: 'failed', isBlocking: true),
        ],
    ));

    $results = app(DoctorManager::class)->failing();

    expect($results)->toBeInstanceOf(Collection::class)
        ->and($results->pluck('key')->all())->toBe(['blocking-failure']);
});

it('returns an empty failing collection when no blocking failures exist', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [
            new FakeDoctorCheck('warning-check', 'yezzmedia/laravel-health', status: 'warning'),
            new FakeDoctorCheck('skipped-check', 'yezzmedia/laravel-health', status: 'skipped'),
            new FakeDoctorCheck('non-blocking-failed', 'yezzmedia/laravel-health', status: 'failed', isBlocking: false),
        ],
    ));

    $results = app(DoctorManager::class)->failing();

    expect($results)->toHaveCount(0);
});

it('ignores registered packages that do not provide doctor checks', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakePlatformPackage);

    $results = app(DoctorManager::class)->run();

    expect($results)->toHaveCount(0);
});

it('ignores disabled packages when collecting doctor checks', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('disabled', 'yezzmedia/laravel-health')],
        enabled: false,
    ));

    $results = app(DoctorManager::class)->run();

    expect($results)->toHaveCount(0)
        ->and(FakeDoctorCheck::executed())->toBe([]);
});

it('rejects doctor checks that belong to another package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('wrong-owner', 'yezzmedia/laravel-other')],
    ));

    app(DoctorManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);

it('rejects doctor checks with empty keys', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('', 'yezzmedia/laravel-health')],
    ));

    app(DoctorManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);

it('rejects doctor results that change the result key', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('health-check', 'yezzmedia/laravel-health', resultKey: 'other-check')],
    ));

    app(DoctorManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);

it('rejects doctor results that change the result package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('health-check', 'yezzmedia/laravel-health', resultPackage: 'yezzmedia/laravel-other')],
    ));

    app(DoctorManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);

it('rejects doctor results with invalid statuses', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [new FakeDoctorCheck('bad-status', 'yezzmedia/laravel-health', resultStatus: 'broken')],
    ));

    app(DoctorManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);
