<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Tests\Fixtures\FakeInstallPackage;
use Tests\Fixtures\FakeInstallStep;
use YezzMedia\Foundation\Events\WebsiteInstalled;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

beforeEach(function (): void {
    FakeInstallStep::reset();
});

it('sorts install steps by priority, package, and step key', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-zeta',
        steps: [
            new FakeInstallStep('z-last', 'yezzmedia/laravel-zeta', priority: 20),
            new FakeInstallStep('a-first', 'yezzmedia/laravel-zeta', priority: 20),
        ],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-alpha',
        steps: [
            new FakeInstallStep('middle', 'yezzmedia/laravel-alpha', priority: 20),
            new FakeInstallStep('priority-win', 'yezzmedia/laravel-alpha', priority: 5),
        ],
    ));

    $result = app(InstallManager::class)->run();

    expect(FakeInstallStep::handled())->toBe([
        'yezzmedia/laravel-alpha:priority-win',
        'yezzmedia/laravel-alpha:middle',
        'yezzmedia/laravel-zeta:a-first',
        'yezzmedia/laravel-zeta:z-last',
    ])
        ->and($result->status)->toBe('success');
});

it('returns a successful empty result when no install steps are registered', function (): void {
    Event::fake([WebsiteInstalled::class]);

    $result = app(InstallManager::class)->run();

    expect($result->status)->toBe('success')
        ->and($result->executedSteps)->toBe([])
        ->and($result->failedSteps)->toBe([])
        ->and($result->messages)->toBe(['No install steps were available.'])
        ->and($result->context)->toBeNull();

    Event::assertDispatched(WebsiteInstalled::class);
});

it('fails fast when an install step throws', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        steps: [
            new FakeInstallStep('first', 'yezzmedia/laravel-install', priority: 10),
            new FakeInstallStep('boom', 'yezzmedia/laravel-install', priority: 20, shouldFail: true),
            new FakeInstallStep('never-runs', 'yezzmedia/laravel-install', priority: 30),
        ],
    ));

    $result = app(InstallManager::class)->run();

    expect($result->status)->toBe('failed')
        ->and($result->executedSteps)->toBe([
            ['package' => 'yezzmedia/laravel-install', 'step' => 'first'],
        ])
        ->and($result->failedSteps)->toBe([
            ['package' => 'yezzmedia/laravel-install', 'step' => 'boom'],
        ])
        ->and(FakeInstallStep::handled())->toBe([
            'yezzmedia/laravel-install:first',
        ]);
});

it('returns a partial result when install is filtered to specific packages', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-install',
        steps: [new FakeInstallStep('selected', 'yezzmedia/laravel-install')],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-other',
        steps: [new FakeInstallStep('ignored', 'yezzmedia/laravel-other')],
    ));

    $result = app(InstallManager::class)->run(['yezzmedia/laravel-install']);

    expect($result->status)->toBe('partial')
        ->and($result->context)->toMatchArray([
            'requested_packages' => ['yezzmedia/laravel-install'],
        ])
        ->and(FakeInstallStep::handled())->toBe([
            'yezzmedia/laravel-install:selected',
        ]);
});

it('returns a partial result when all install steps are skipped', function (): void {
    Event::fake([WebsiteInstalled::class]);

    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('skipped', 'yezzmedia/laravel-install', shouldRun: false)],
    ));

    $result = app(InstallManager::class)->run();

    expect($result->status)->toBe('partial')
        ->and($result->executedSteps)->toBe([])
        ->and($result->failedSteps)->toBe([])
        ->and($result->context)->toMatchArray([
            'skipped_steps' => [
                ['package' => 'yezzmedia/laravel-install', 'step' => 'skipped'],
            ],
        ])
        ->and(FakeInstallStep::handled())->toBe([]);

    Event::assertNotDispatched(WebsiteInstalled::class);
});

it('includes requested packages and skipped steps in the same partial context', function (): void {
    Event::fake([WebsiteInstalled::class]);

    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-install',
        steps: [new FakeInstallStep('skipped', 'yezzmedia/laravel-install', shouldRun: false)],
    ));

    $result = app(InstallManager::class)->run(['yezzmedia/laravel-install']);

    expect($result->status)->toBe('partial')
        ->and($result->context)->toMatchArray([
            'requested_packages' => ['yezzmedia/laravel-install'],
            'skipped_steps' => [
                ['package' => 'yezzmedia/laravel-install', 'step' => 'skipped'],
            ],
        ]);

    Event::assertNotDispatched(WebsiteInstalled::class);
});

it('returns a partial empty result when filtering unknown packages', function (): void {
    Event::fake([WebsiteInstalled::class]);

    $result = app(InstallManager::class)->run(['yezzmedia/laravel-missing']);

    expect($result->status)->toBe('partial')
        ->and($result->executedSteps)->toBe([])
        ->and($result->failedSteps)->toBe([])
        ->and($result->messages)->toBe(['No install steps were available.'])
        ->and($result->context)->toMatchArray([
            'requested_packages' => ['yezzmedia/laravel-missing'],
        ]);

    Event::assertNotDispatched(WebsiteInstalled::class);
});

it('returns only sorted steps for the requested package', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-install',
        steps: [
            new FakeInstallStep('late', 'yezzmedia/laravel-install', priority: 20),
            new FakeInstallStep('early', 'yezzmedia/laravel-install', priority: 10),
        ],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-other',
        steps: [new FakeInstallStep('ignored', 'yezzmedia/laravel-other')],
    ));

    $steps = app(InstallManager::class)->stepsFor('yezzmedia/laravel-install');

    expect(array_map(static fn (InstallStep $step): string => $step->key(), $steps))->toBe([
        'early',
        'late',
    ]);
});

it('ignores disabled packages when collecting install steps', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('disabled', 'yezzmedia/laravel-install')],
        enabled: false,
    ));

    $result = app(InstallManager::class)->run();

    expect($result->executedSteps)->toBe([])
        ->and($result->messages)->toBe(['No install steps were available.']);
});

it('dispatches website installed only on successful runs', function (): void {
    Event::fake([WebsiteInstalled::class]);

    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('successful', 'yezzmedia/laravel-install')],
    ));

    $result = app(InstallManager::class)->run();

    expect($result->status)->toBe('success');

    Event::assertDispatched(WebsiteInstalled::class);
});

it('rejects install steps that belong to another package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('wrong-owner', 'yezzmedia/laravel-other')],
    ));

    app(InstallManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);

it('rejects install steps with empty keys', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('', 'yezzmedia/laravel-install')],
    ));

    app(InstallManager::class)->run();
})->throws(InvalidPackageDefinitionException::class);
