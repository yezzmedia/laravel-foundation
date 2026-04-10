<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Tests\Fixtures\FakeInstallPackage;
use Tests\Fixtures\FakeInstallStep;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Events\WebsiteInstalled;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Install\AuditInstallStep;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

beforeEach(function (): void {
    FakeInstallStep::reset();
});

function fakeAuditEventDefinition(string $package): AuditEventDefinition
{
    return new AuditEventDefinition(
        key: str_replace('/', '.', $package).'.audit.updated',
        package: $package,
        action: 'updated',
        subjectType: 'audit_subject',
        description: 'Fake audit event.',
    );
}

function fakeAuditInstallStep(string $key, string $package, int $priority = 10): InstallStep
{
    return new class($key, $package, $priority) extends FakeInstallStep implements AuditInstallStep {};
}

function provisionFoundationInstallBootstrapApp(): void
{
    $bootstrapDirectory = base_path('bootstrap');

    if (! is_dir($bootstrapDirectory)) {
        mkdir($bootstrapDirectory, 0777, true);
    }

    file_put_contents(base_path('bootstrap/app.php'), <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->create();
PHP);
}

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

    Event::assertDispatched(WebsiteInstalled::class, static fn (WebsiteInstalled $event): bool => $event->status === 'success' && $event->executedStepCount === 0 && $event->failedStepCount === 0 && $event->context === null);
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

it('passes the explicit install context into executed steps', function (): void {
    provisionFoundationInstallBootstrapApp();

    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));

    $result = app(InstallManager::class)->run(context: new InstallContext(
        allowMigrations: true,
        refreshPublishedResources: true,
        configureAccessAudit: true,
        configureHttpMiddlewareBridge: true,
    ));

    expect($result->status)->toBe('success')
        ->and($result->context)->toBe([
            'allow_migrations' => true,
            'refresh_published_resources' => true,
            'configure_http_middleware_bridge' => true,
            'configure_audit' => true,
            'configure_access_audit' => true,
            'audit_packages' => ['yezzmedia/laravel-access'],
        ])
        ->and(FakeInstallStep::handledContexts())->toBe([
            [
                'reference' => 'yezzmedia/laravel-install:bootstrap',
                'allow_migrations' => true,
                'refresh_published_resources' => true,
                'configure_access_audit' => true,
                'configure_audit' => true,
                'configure_http_middleware_bridge' => true,
                'audit_packages' => ['yezzmedia/laravel-access'],
            ],
        ]);
});

it('includes the http middleware bridge flag in install result context', function (): void {
    provisionFoundationInstallBootstrapApp();

    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install')],
    ));

    $result = app(InstallManager::class)->run(context: new InstallContext(
        configureHttpMiddlewareBridge: true,
    ));

    expect($result->status)->toBe('success')
        ->and($result->context)->toBe([
            'configure_http_middleware_bridge' => true,
        ]);
});

it('lists audit-capable packages in sorted order', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-zeta',
        steps: [fakeAuditInstallStep('configure_audit', 'yezzmedia/laravel-zeta')],
        auditEvents: [fakeAuditEventDefinition('yezzmedia/laravel-zeta')],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-alpha',
        steps: [fakeAuditInstallStep('configure_audit', 'yezzmedia/laravel-alpha')],
        auditEvents: [fakeAuditEventDefinition('yezzmedia/laravel-alpha')],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-no-audit-step',
        steps: [new FakeInstallStep('bootstrap', 'yezzmedia/laravel-no-audit-step')],
        auditEvents: [fakeAuditEventDefinition('yezzmedia/laravel-no-audit-step')],
    ));

    expect(app(InstallManager::class)->auditPackages())->toBe([
        'yezzmedia/laravel-alpha',
        'yezzmedia/laravel-zeta',
    ]);
});

it('runs only audit install steps for selected audit packages', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-install',
        steps: [
            new FakeInstallStep('bootstrap', 'yezzmedia/laravel-install', priority: 5),
            fakeAuditInstallStep('configure_audit', 'yezzmedia/laravel-install', 10),
        ],
        auditEvents: [fakeAuditEventDefinition('yezzmedia/laravel-install')],
    ));

    $registrar->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-other',
        steps: [fakeAuditInstallStep('configure_audit', 'yezzmedia/laravel-other')],
        auditEvents: [fakeAuditEventDefinition('yezzmedia/laravel-other')],
    ));

    $result = app(InstallManager::class)->runAudit(
        ['yezzmedia/laravel-install'],
        new InstallContext(configureAudit: true, auditPackages: ['yezzmedia/laravel-install']),
    );

    expect($result->status)->toBe('partial')
        ->and($result->executedSteps)->toBe([
            ['package' => 'yezzmedia/laravel-install', 'step' => 'configure_audit'],
        ])
        ->and($result->context)->toBe([
            'requested_packages' => ['yezzmedia/laravel-install'],
            'configure_audit' => true,
            'audit_packages' => ['yezzmedia/laravel-install'],
        ])
        ->and(FakeInstallStep::handled())->toBe([
            'yezzmedia/laravel-install:configure_audit',
        ]);
});

it('passes the selected audit packages into audit runs', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        name: 'yezzmedia/laravel-install',
        steps: [fakeAuditInstallStep('configure_audit', 'yezzmedia/laravel-install')],
        auditEvents: [fakeAuditEventDefinition('yezzmedia/laravel-install')],
    ));

    $result = app(InstallManager::class)->runAudit(context: new InstallContext(
        configureAudit: true,
        auditPackages: ['yezzmedia/laravel-install'],
    ));

    expect($result->status)->toBe('success')
        ->and($result->context)->toBe([
            'configure_audit' => true,
            'audit_packages' => ['yezzmedia/laravel-install'],
        ])
        ->and(FakeInstallStep::handledContexts())->toBe([
            [
                'reference' => 'yezzmedia/laravel-install:configure_audit',
                'allow_migrations' => false,
                'refresh_published_resources' => false,
                'configure_access_audit' => false,
                'configure_audit' => true,
                'configure_http_middleware_bridge' => false,
                'audit_packages' => ['yezzmedia/laravel-install'],
            ],
        ]);
});

it('skips migration-gated steps when migrations are not allowed', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('database', 'yezzmedia/laravel-install', requiresMigrations: true)],
    ));

    $result = app(InstallManager::class)->run();

    expect($result->status)->toBe('partial')
        ->and($result->executedSteps)->toBe([])
        ->and($result->context)->toMatchArray([
            'skipped_steps' => [
                ['package' => 'yezzmedia/laravel-install', 'step' => 'database'],
            ],
        ])
        ->and(FakeInstallStep::handled())->toBe([]);
});

it('ignores optional install steps when they are not requested', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('audit', 'yezzmedia/laravel-install', optional: true, shouldRun: false)],
    ));

    $result = app(InstallManager::class)->run();

    expect($result->status)->toBe('success')
        ->and($result->messages)->toBe(['No install steps were available.'])
        ->and($result->context)->toBeNull()
        ->and(FakeInstallStep::handled())->toBe([]);
});

it('runs migration-gated steps when migrations are explicitly allowed', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeInstallPackage(
        steps: [new FakeInstallStep('database', 'yezzmedia/laravel-install', requiresMigrations: true)],
    ));

    $result = app(InstallManager::class)->run(context: new InstallContext(allowMigrations: true));

    expect($result->status)->toBe('success')
        ->and($result->executedSteps)->toBe([
            ['package' => 'yezzmedia/laravel-install', 'step' => 'database'],
        ])
        ->and($result->context)->toBe([
            'allow_migrations' => true,
        ])
        ->and(FakeInstallStep::handled())->toBe([
            'yezzmedia/laravel-install:database',
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

    Event::assertDispatched(WebsiteInstalled::class, static fn (WebsiteInstalled $event): bool => $event->status === 'success' && $event->executedStepCount === 1 && $event->failedStepCount === 0);
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
