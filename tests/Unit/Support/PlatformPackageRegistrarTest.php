<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Tests\Fixtures\FakeCapabilityPackage;
use Tests\Fixtures\FakeFeaturePackage;
use Tests\Fixtures\FakePlatformPackage;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\CacheProfile;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Events\FeatureRegistered;
use YezzMedia\Foundation\Events\OpsModuleDefined;
use YezzMedia\Foundation\Events\PackageRegistered;
use YezzMedia\Foundation\Events\PermissionDefined;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

it('registers package metadata and features through the registrar', function (): void {
    Event::fake([PackageRegistered::class, FeatureRegistered::class]);

    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage);

    expect(app(PackageRegistry::class)->has('yezzmedia/laravel-content'))->toBeTrue()
        ->and(app(FeatureRegistry::class)->has('content.pages'))->toBeTrue();

    Event::assertDispatched(PackageRegistered::class);
    Event::assertDispatched(FeatureRegistered::class);
});

it('does not register features for disabled packages', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage(enabled: false));

    expect(app(PackageRegistry::class)->has('yezzmedia/laravel-content'))->toBeTrue()
        ->and(app(FeatureRegistry::class)->has('content.pages'))->toBeFalse();
});

it('rejects duplicate package registration', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);
    $package = new FakePlatformPackage;

    $registrar->register($package);
    $registrar->register($package);
})->throws(InvalidPackageDefinitionException::class);

it('rejects packages without vendors', function (): void {
    app(PlatformPackageRegistrar::class)->register(new class implements PlatformPackage
    {
        public function metadata(): PackageMetadata
        {
            return new PackageMetadata(
                name: 'yezzmedia/laravel-invalid',
                vendor: '',
                description: 'Invalid package.',
                packageClass: self::class,
            );
        }
    });
})->throws(InvalidPackageDefinitionException::class);

it('rejects packages without descriptions', function (): void {
    app(PlatformPackageRegistrar::class)->register(new class implements PlatformPackage
    {
        public function metadata(): PackageMetadata
        {
            return new PackageMetadata(
                name: 'yezzmedia/laravel-invalid',
                vendor: 'yezzmedia',
                description: '',
                packageClass: self::class,
            );
        }
    });
})->throws(InvalidPackageDefinitionException::class);

it('rejects packages with mismatched package classes', function (): void {
    app(PlatformPackageRegistrar::class)->register(new class implements PlatformPackage
    {
        public function metadata(): PackageMetadata
        {
            return new PackageMetadata(
                name: 'yezzmedia/laravel-invalid',
                vendor: 'yezzmedia',
                description: 'Invalid package.',
                packageClass: FakePlatformPackage::class,
            );
        }
    });
})->throws(InvalidPackageDefinitionException::class);

it('rejects feature definitions that belong to another package', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeFeaturePackage(featurePackage: 'yezzmedia/laravel-other'));
})->throws(InvalidPackageDefinitionException::class);

it('registers permissions and ops modules through the registrar', function (): void {
    Event::fake([PermissionDefined::class, OpsModuleDefined::class]);

    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeCapabilityPackage(
        permissions: [
            new PermissionDefinition('ops.audit.view', 'yezzmedia/laravel-ops', 'View audit logs'),
        ],
        opsModules: [
            new OpsModuleDefinition('ops.audit', 'yezzmedia/laravel-ops', 'Audit trail', 'page', 'ops.audit.view'),
        ],
    ));

    expect(app(PermissionRegistry::class)->all()->pluck('name')->all())->toBe(['ops.audit.view'])
        ->and(app(OpsModuleRegistry::class)->all()->pluck('key')->all())->toBe(['ops.audit']);

    Event::assertDispatched(PermissionDefined::class);
    Event::assertDispatched(OpsModuleDefined::class);
});

it('validates audit, rate limit, and cache declarations during package registration', function (): void {
    $registrar = app(PlatformPackageRegistrar::class);

    $registrar->register(new FakeCapabilityPackage(
        auditEvents: [
            new AuditEventDefinition(
                key: 'content.page.published',
                package: 'yezzmedia/laravel-ops',
                action: 'published',
                subjectType: 'page',
                description: 'A page was published.',
                contextKeys: ['page_id', 'actor_id'],
            ),
        ],
        rateLimiters: [
            new RateLimitDefinition(
                key: 'ops.login',
                package: 'yezzmedia/laravel-ops',
                description: 'Protect admin logins.',
                maxAttempts: 5,
                decaySeconds: 60,
                scope: 'ip_user',
                keyStrategy: 'ip-and-user',
            ),
        ],
        cacheProfiles: [
            new CacheProfile(
                key: 'navigation.tree',
                package: 'yezzmedia/laravel-ops',
                prefix: 'navigation',
                tags: ['navigation'],
                ttl: 300,
                invalidationEvents: ['content.page.saved'],
                description: 'Navigation tree cache.',
            ),
        ],
    ));

    expect(app(PackageRegistry::class)->has('yezzmedia/laravel-ops'))->toBeTrue();
});

it('rejects permissions that belong to another package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        permissions: [
            new PermissionDefinition('ops.audit.view', 'yezzmedia/laravel-other', 'View audit logs'),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('does not register capability declarations for disabled packages', function (): void {
    Event::fake([PermissionDefined::class, OpsModuleDefined::class]);

    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        permissions: [
            new PermissionDefinition('ops.audit.view', 'yezzmedia/laravel-ops', 'View audit logs'),
        ],
        opsModules: [
            new OpsModuleDefinition('ops.audit', 'yezzmedia/laravel-ops', 'Audit trail', 'page'),
        ],
        enabled: false,
    ));

    expect(app(PermissionRegistry::class)->all())->toHaveCount(0)
        ->and(app(OpsModuleRegistry::class)->all())->toHaveCount(0);

    Event::assertNotDispatched(PermissionDefined::class);
    Event::assertNotDispatched(OpsModuleDefined::class);
});

it('rejects permissions without labels', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        permissions: [
            new PermissionDefinition('ops.audit.view', 'yezzmedia/laravel-ops', ''),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects ops modules without types', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        opsModules: [
            new OpsModuleDefinition('ops.audit', 'yezzmedia/laravel-ops', 'Audit trail', ''),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects audit events with missing completion metadata', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        auditEvents: [
            new AuditEventDefinition(
                key: 'content.page.published',
                package: 'yezzmedia/laravel-ops',
                action: '',
                subjectType: 'page',
                description: 'A page was published.',
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects audit events that belong to another package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        auditEvents: [
            new AuditEventDefinition(
                key: 'content.page.published',
                package: 'yezzmedia/laravel-other',
                action: 'published',
                subjectType: 'page',
                description: 'A page was published.',
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects cache profiles with invalid ttl', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        cacheProfiles: [
            new CacheProfile(
                key: 'navigation.tree',
                package: 'yezzmedia/laravel-ops',
                prefix: 'navigation',
                tags: ['navigation'],
                ttl: 0,
                invalidationEvents: ['content.page.saved'],
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects cache profiles that belong to another package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        cacheProfiles: [
            new CacheProfile(
                key: 'navigation.tree',
                package: 'yezzmedia/laravel-other',
                prefix: 'navigation',
                tags: ['navigation'],
                ttl: 300,
                invalidationEvents: ['content.page.saved'],
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects cache profiles with empty invalidation events', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        cacheProfiles: [
            new CacheProfile(
                key: 'navigation.tree',
                package: 'yezzmedia/laravel-ops',
                prefix: 'navigation',
                tags: ['navigation'],
                ttl: 300,
                invalidationEvents: [''],
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects invalid rate limiter definitions', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        rateLimiters: [
            new RateLimitDefinition(
                key: 'ops.login',
                package: 'yezzmedia/laravel-ops',
                description: 'Protect admin logins.',
                maxAttempts: 0,
                decaySeconds: 60,
                scope: 'ip',
                keyStrategy: 'ip-only',
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects rate limiters that belong to another package', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        rateLimiters: [
            new RateLimitDefinition(
                key: 'ops.login',
                package: 'yezzmedia/laravel-other',
                description: 'Protect admin logins.',
                maxAttempts: 5,
                decaySeconds: 60,
                scope: 'ip',
                keyStrategy: 'ip-only',
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects rate limiters without key strategies', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        rateLimiters: [
            new RateLimitDefinition(
                key: 'ops.login',
                package: 'yezzmedia/laravel-ops',
                description: 'Protect admin logins.',
                maxAttempts: 5,
                decaySeconds: 60,
                scope: 'ip',
                keyStrategy: '',
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);

it('rejects unsupported rate limiter scopes', function (): void {
    app(PlatformPackageRegistrar::class)->register(new FakeCapabilityPackage(
        rateLimiters: [
            new RateLimitDefinition(
                key: 'ops.login',
                package: 'yezzmedia/laravel-ops',
                description: 'Protect admin logins.',
                maxAttempts: 5,
                decaySeconds: 60,
                scope: 'tenant',
                keyStrategy: 'tenant-only',
            ),
        ],
    ));
})->throws(InvalidPackageDefinitionException::class);
