<?php

declare(strict_types=1);

use Tests\Fixtures\FakeDoctorCheck;
use Tests\Fixtures\FakeDoctorPackage;
use Tests\Fixtures\FakeFeaturePackage;
use Tests\Fixtures\FakeInstallPackage;
use Tests\Fixtures\FakeInstallStep;
use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    FakeDoctorCheck::reset();
    FakeInstallStep::reset();
});

it('lists registered packages', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakePlatformPackage);
    $registrar->register(new FakeFeaturePackage);

    $command = artisan('website:packages');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:packages.');
    }

    $command
        ->expectsTable([
            'Package',
            'Vendor',
            'Enabled',
            'Priority',
        ], [
            ['yezzmedia/laravel-content', 'yezzmedia', 'yes', 10],
            ['yezzmedia/laravel-foundation', 'yezzmedia', 'yes', 0],
            ['yezzmedia/laravel-settings', 'yezzmedia', 'yes', 10],
        ])
        ->assertSuccessful();
});

it('lists the foundation package even without consumer package registration', function (): void {
    $command = artisan('website:packages');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:packages.');
    }

    $command
        ->expectsTable([
            'Package',
            'Vendor',
            'Enabled',
            'Priority',
        ], [
            ['yezzmedia/laravel-foundation', 'yezzmedia', 'yes', 0],
        ])
        ->assertSuccessful();
});

it('lists registered features', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeFeaturePackage);

    $command = artisan('website:features');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:features.');
    }

    $command
        ->expectsTable([
            'Feature',
            'Package',
            'Label',
        ], [
            ['content.pages', 'yezzmedia/laravel-content', 'Content pages'],
        ])
        ->assertSuccessful();
});

it('shows a helpful message when no features are registered', function (): void {
    $command = artisan('website:features');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:features.');
    }

    $command
        ->expectsOutputToContain('No features registered.')
        ->assertSuccessful();
});

it('runs install steps and reports a successful status', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));

    $command = artisan('website:install');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Status: success')
        ->expectsOutputToContain('Executed install step [bootstrap] for package [yezzmedia/laravel-install].')
        ->assertSuccessful();
});

it('reports when migration execution is explicitly enabled', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('database', 'yezzmedia/laravel-install', requiresMigrations: true)],
    ));

    $command = artisan('website:install', ['--migrate' => true]);

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Migration execution is enabled for this install run.')
        ->expectsOutputToContain('Status: success')
        ->expectsOutputToContain('Executed install step [database] for package [yezzmedia/laravel-install].')
        ->assertSuccessful();
});

it('reports when published resource refresh is explicitly enabled', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));

    $command = artisan('website:install', ['--refresh-publish' => true]);

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Published resource refresh is enabled for this install run.')
        ->expectsOutputToContain('Status: success')
        ->expectsOutputToContain('Executed install step [bootstrap] for package [yezzmedia/laravel-install].')
        ->assertSuccessful();
});

it('reports when access audit persistence is explicitly enabled', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));

    $command = artisan('website:install', ['--configure-access-audit' => true]);

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Access audit persistence is enabled for this install run.')
        ->expectsOutputToContain('Status: success')
        ->expectsOutputToContain('Executed install step [bootstrap] for package [yezzmedia/laravel-install].')
        ->assertSuccessful();
});

it('skips migration-gated install steps during ordinary install runs', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('database', 'yezzmedia/laravel-install', requiresMigrations: true)],
    ));

    $command = artisan('website:install');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Status: partial')
        ->expectsOutputToContain('Skipped install step [database] for package [yezzmedia/laravel-install].')
        ->assertSuccessful();
});

it('runs filtered install steps only for requested packages', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-install',
        steps: [new FakeInstallStep('selected', 'yezzmedia/laravel-install')],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-other',
        steps: [new FakeInstallStep('ignored', 'yezzmedia/laravel-other')],
    ));

    $command = artisan('website:install', ['--only' => ['yezzmedia/laravel-install']]);

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Status: partial')
        ->expectsOutputToContain('Requested packages: yezzmedia/laravel-install')
        ->doesntExpectOutputToContain('yezzmedia/laravel-other')
        ->assertSuccessful();
});

it('fails the install command when a blocking install step fails', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('boom', 'yezzmedia/laravel-install', shouldFail: true)],
    ));

    $command = artisan('website:install');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:install.');
    }

    $command
        ->expectsOutputToContain('Status: failed')
        ->expectsOutputToContain('Install step [boom] for package [yezzmedia/laravel-install] failed.')
        ->assertFailed();
});

it('runs doctor checks and succeeds without blocking failures', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [
            new FakeDoctorCheck('config', 'yezzmedia/laravel-health', status: 'passed'),
            new FakeDoctorCheck('queue', 'yezzmedia/laravel-health', status: 'warning', message: 'Queue worker is not running.'),
        ],
    ));

    $command = artisan('website:doctor');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:doctor.');
    }

    $command
        ->expectsOutputToContain('Summary: passed=1 warning=1 failed=0 skipped=0')
        ->assertSuccessful();
});

it('fails doctor when a blocking check fails', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeDoctorPackage(
        checks: [
            new FakeDoctorCheck('config', 'yezzmedia/laravel-health', status: 'failed', isBlocking: true, message: 'Configuration is invalid.'),
        ],
    ));

    $command = artisan('website:doctor');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:doctor.');
    }

    $command
        ->expectsOutputToContain('Summary: passed=0 warning=0 failed=1 skipped=0')
        ->assertFailed();
});

it('shows a helpful message when no doctor checks are registered', function (): void {
    $command = artisan('website:doctor');

    if (is_int($command)) {
        throw new RuntimeException('Expected pending command for website:doctor.');
    }

    $command
        ->expectsOutputToContain('No doctor checks registered.')
        ->assertSuccessful();
});
