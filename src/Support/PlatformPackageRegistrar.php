<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesCacheProfiles;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\DefinesRateLimiters;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\CacheProfile;
use YezzMedia\Foundation\Data\FeatureDefinition;
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

/**
 * Normalizes one package's declarations into the foundation registries.
 */
class PlatformPackageRegistrar
{
    public function __construct(
        private readonly PackageRegistry $packages,
        private readonly FeatureRegistry $features,
        private readonly PermissionRegistry $permissions,
        private readonly OpsModuleRegistry $opsModules,
        private readonly PackageManifestLoader $manifestLoader,
    ) {}

    public function register(PlatformPackage $package): void
    {
        $metadata = $package->metadata();

        $this->ensureValidPackageMetadata($package, $metadata);

        $this->packages->register($metadata);

        event(new PackageRegistered($metadata->name));

        if (! $metadata->enabled) {
            return;
        }

        // Only enabled packages participate in runtime workflows and capability aggregation.
        $this->manifestLoader->register($package);

        if ($package instanceof RegistersFeatures) {
            foreach ($package->featureDefinitions() as $featureDefinition) {
                $this->ensureValidFeatureDefinition($metadata, $featureDefinition);
                $this->features->register($featureDefinition);

                event(new FeatureRegistered($featureDefinition->name, $featureDefinition->package));
            }
        }

        if ($package instanceof DefinesPermissions) {
            foreach ($package->permissionDefinitions() as $permissionDefinition) {
                $this->ensureValidPermissionDefinition($metadata, $permissionDefinition);
                $this->permissions->register($permissionDefinition);

                event(new PermissionDefined($permissionDefinition->name, $permissionDefinition->package));
            }
        }

        if ($package instanceof ProvidesOpsModules) {
            foreach ($package->opsModuleDefinitions() as $opsModuleDefinition) {
                $this->ensureValidOpsModuleDefinition($metadata, $opsModuleDefinition);
                $this->opsModules->register($opsModuleDefinition);

                event(new OpsModuleDefined($opsModuleDefinition->key, $opsModuleDefinition->package));
            }
        }

        if ($package instanceof DefinesAuditEvents) {
            foreach ($package->auditEventDefinitions() as $auditEventDefinition) {
                $this->ensureValidAuditEventDefinition($metadata, $auditEventDefinition);
            }
        }

        if ($package instanceof DefinesRateLimiters) {
            foreach ($package->rateLimitDefinitions() as $rateLimitDefinition) {
                $this->ensureValidRateLimitDefinition($metadata, $rateLimitDefinition);
            }
        }

        if ($package instanceof DefinesCacheProfiles) {
            foreach ($package->cacheProfiles() as $cacheProfile) {
                $this->ensureValidCacheProfile($metadata, $cacheProfile);
            }
        }
    }

    private function ensureValidAuditEventDefinition(PackageMetadata $metadata, AuditEventDefinition $auditEventDefinition): void
    {
        if ($auditEventDefinition->key === '') {
            throw new InvalidPackageDefinitionException('Audit event key must not be empty.');
        }

        if ($auditEventDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Audit event [%s] must belong to package [%s].',
                $auditEventDefinition->key,
                $metadata->name,
            ));
        }

        if ($auditEventDefinition->action === '' || $auditEventDefinition->subjectType === '' || $auditEventDefinition->description === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'Audit event [%s] must define action, subject type, and description.',
                $auditEventDefinition->key,
            ));
        }
    }

    private function ensureValidCacheProfile(PackageMetadata $metadata, CacheProfile $cacheProfile): void
    {
        if ($cacheProfile->key === '') {
            throw new InvalidPackageDefinitionException('Cache profile key must not be empty.');
        }

        if ($cacheProfile->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Cache profile [%s] must belong to package [%s].',
                $cacheProfile->key,
                $metadata->name,
            ));
        }

        if ($cacheProfile->prefix === '' || $cacheProfile->ttl <= 0) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Cache profile [%s] must define a prefix and positive ttl.',
                $cacheProfile->key,
            ));
        }

        foreach ($cacheProfile->invalidationEvents as $event) {
            if ($event === '') {
                throw new InvalidPackageDefinitionException(sprintf(
                    'Cache profile [%s] invalidation events must not be empty.',
                    $cacheProfile->key,
                ));
            }
        }
    }

    private function ensureValidPackageMetadata(PlatformPackage $package, PackageMetadata $metadata): void
    {
        if ($metadata->vendor === '') {
            throw new InvalidPackageDefinitionException('Package vendor must not be empty.');
        }

        if ($metadata->description === '') {
            throw new InvalidPackageDefinitionException('Package description must not be empty.');
        }

        if ($metadata->packageClass !== $package::class) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Package class [%s] does not match descriptor [%s].',
                $metadata->packageClass,
                $package::class,
            ));
        }
    }

    private function ensureValidFeatureDefinition(PackageMetadata $metadata, FeatureDefinition $featureDefinition): void
    {
        if ($featureDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Feature [%s] must belong to package [%s].',
                $featureDefinition->name,
                $metadata->name,
            ));
        }

        if ($featureDefinition->label === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'Feature [%s] must define a label.',
                $featureDefinition->name,
            ));
        }
    }

    private function ensureValidOpsModuleDefinition(PackageMetadata $metadata, OpsModuleDefinition $opsModuleDefinition): void
    {
        if ($opsModuleDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Ops module [%s] must belong to package [%s].',
                $opsModuleDefinition->key,
                $metadata->name,
            ));
        }

        if ($opsModuleDefinition->label === '' || $opsModuleDefinition->type === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'Ops module [%s] must define a label and type.',
                $opsModuleDefinition->key,
            ));
        }
    }

    private function ensureValidPermissionDefinition(PackageMetadata $metadata, PermissionDefinition $permissionDefinition): void
    {
        if ($permissionDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Permission [%s] must belong to package [%s].',
                $permissionDefinition->name,
                $metadata->name,
            ));
        }

        if ($permissionDefinition->label === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'Permission [%s] must define a label.',
                $permissionDefinition->name,
            ));
        }
    }

    private function ensureValidRateLimitDefinition(PackageMetadata $metadata, RateLimitDefinition $rateLimitDefinition): void
    {
        if ($rateLimitDefinition->key === '') {
            throw new InvalidPackageDefinitionException('Rate limiter key must not be empty.');
        }

        if ($rateLimitDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Rate limiter [%s] must belong to package [%s].',
                $rateLimitDefinition->key,
                $metadata->name,
            ));
        }

        if ($rateLimitDefinition->description === '' || $rateLimitDefinition->keyStrategy === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'Rate limiter [%s] must define a description and key strategy.',
                $rateLimitDefinition->key,
            ));
        }

        if ($rateLimitDefinition->maxAttempts <= 0 || $rateLimitDefinition->decaySeconds <= 0) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Rate limiter [%s] must define positive attempts and decay seconds.',
                $rateLimitDefinition->key,
            ));
        }

        if (! in_array($rateLimitDefinition->scope, ['ip', 'user', 'ip_user', 'custom'], true)) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Rate limiter [%s] has unsupported scope [%s].',
                $rateLimitDefinition->key,
                $rateLimitDefinition->scope,
            ));
        }
    }
}
