<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesCacheProfiles;
use YezzMedia\Foundation\Contracts\DefinesHttpMiddleware;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\DefinesRateLimiters;
use YezzMedia\Foundation\Contracts\DefinesSecurityRequests;
use YezzMedia\Foundation\Contracts\DefinesSecurityRequirements;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\CacheProfile;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Data\SecurityRequestDefinition;
use YezzMedia\Foundation\Data\SecurityRequirementDefinition;
use YezzMedia\Foundation\Events\FeatureRegistered;
use YezzMedia\Foundation\Events\OpsModuleDefined;
use YezzMedia\Foundation\Events\PackageRegistered;
use YezzMedia\Foundation\Events\PermissionDefined;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\HttpMiddlewareRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Registry\SecurityRequestRegistry;
use YezzMedia\Foundation\Registry\SecurityRequirementRegistry;

/**
 * Normalizes one package's declarations into the foundation registries.
 */
class PlatformPackageRegistrar
{
    public function __construct(
        private readonly PackageRegistry $packages,
        private readonly FeatureRegistry $features,
        private readonly HttpMiddlewareRegistry $httpMiddleware,
        private readonly PermissionRegistry $permissions,
        private readonly OpsModuleRegistry $opsModules,
        private readonly SecurityRequestRegistry $securityRequests,
        private readonly SecurityRequirementRegistry $securityRequirements,
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

        if ($package instanceof DefinesHttpMiddleware) {
            foreach ($package->httpMiddlewareDefinitions() as $httpMiddlewareDefinition) {
                $this->ensureValidHttpMiddlewareDefinition($metadata, $httpMiddlewareDefinition);

                if (! $httpMiddlewareDefinition->enabled) {
                    continue;
                }

                $this->httpMiddleware->register($httpMiddlewareDefinition);
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

        if ($package instanceof DefinesSecurityRequests) {
            foreach ($package->securityRequestDefinitions() as $securityRequestDefinition) {
                $this->ensureValidSecurityRequestDefinition($metadata, $securityRequestDefinition);
                $this->securityRequests->register($securityRequestDefinition);
            }
        }

        if ($package instanceof DefinesSecurityRequirements) {
            foreach ($package->securityRequirementDefinitions() as $securityRequirementDefinition) {
                $this->ensureValidSecurityRequirementDefinition($metadata, $securityRequirementDefinition);
                $this->securityRequirements->register($securityRequirementDefinition);
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

    private function ensureValidSecurityRequestDefinition(PackageMetadata $metadata, SecurityRequestDefinition $securityRequestDefinition): void
    {
        if ($securityRequestDefinition->key === '') {
            throw new InvalidPackageDefinitionException('Security request key must not be empty.');
        }

        if ($securityRequestDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security request [%s] must belong to package [%s].',
                $securityRequestDefinition->key,
                $metadata->name,
            ));
        }

        if (
            $securityRequestDefinition->domain === ''
            || $securityRequestDefinition->control === ''
            || $securityRequestDefinition->scope === ''
            || $securityRequestDefinition->requestedLevel === ''
            || $securityRequestDefinition->requestedEnforcementMode === ''
            || $securityRequestDefinition->description === ''
        ) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security request [%s] must define domain, control, scope, requested level, requested enforcement mode, and description.',
                $securityRequestDefinition->key,
            ));
        }

        $this->ensureValidSecurityDomain($securityRequestDefinition->key, $securityRequestDefinition->domain);
        $this->ensureValidSecurityLevel($securityRequestDefinition->key, $securityRequestDefinition->requestedLevel, 'requested level');
        $this->ensureValidSecurityEnforcementMode($securityRequestDefinition->key, $securityRequestDefinition->requestedEnforcementMode, 'requested enforcement mode');

        foreach ($securityRequestDefinition->payloadSchema as $field => $description) {
            if (! is_string($field) || $field === '' || ! is_string($description) || $description === '') {
                throw new InvalidPackageDefinitionException(sprintf(
                    'Security request [%s] payload schema entries must define non-empty field names and descriptions.',
                    $securityRequestDefinition->key,
                ));
            }
        }

        foreach ($securityRequestDefinition->allowedPreviewFields as $field) {
            if ($field === '' || ! array_key_exists($field, $securityRequestDefinition->payloadSchema)) {
                throw new InvalidPackageDefinitionException(sprintf(
                    'Security request [%s] preview field [%s] must exist in the payload schema.',
                    $securityRequestDefinition->key,
                    $field,
                ));
            }
        }

        foreach ($securityRequestDefinition->maskedFields as $field) {
            if ($field === '' || ! array_key_exists($field, $securityRequestDefinition->payloadSchema)) {
                throw new InvalidPackageDefinitionException(sprintf(
                    'Security request [%s] masked field [%s] must exist in the payload schema.',
                    $securityRequestDefinition->key,
                    $field,
                ));
            }
        }
    }

    private function ensureValidSecurityRequirementDefinition(PackageMetadata $metadata, SecurityRequirementDefinition $securityRequirementDefinition): void
    {
        if ($securityRequirementDefinition->key === '') {
            throw new InvalidPackageDefinitionException('Security requirement key must not be empty.');
        }

        if ($securityRequirementDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security requirement [%s] must belong to package [%s].',
                $securityRequirementDefinition->key,
                $metadata->name,
            ));
        }

        if (
            $securityRequirementDefinition->domain === ''
            || $securityRequirementDefinition->control === ''
            || $securityRequirementDefinition->scope === ''
            || $securityRequirementDefinition->level === ''
            || $securityRequirementDefinition->enforcementMode === ''
            || $securityRequirementDefinition->description === ''
        ) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security requirement [%s] must define domain, control, scope, level, enforcement mode, and description.',
                $securityRequirementDefinition->key,
            ));
        }

        $this->ensureValidSecurityDomain($securityRequirementDefinition->key, $securityRequirementDefinition->domain);
        $this->ensureValidSecurityLevel($securityRequirementDefinition->key, $securityRequirementDefinition->level, 'level');
        $this->ensureValidSecurityEnforcementMode($securityRequirementDefinition->key, $securityRequirementDefinition->enforcementMode, 'enforcement mode');

        foreach ($securityRequirementDefinition->appliesTo as $appliesTo) {
            if ($appliesTo === '') {
                throw new InvalidPackageDefinitionException(sprintf(
                    'Security requirement [%s] applies-to entries must not be empty.',
                    $securityRequirementDefinition->key,
                ));
            }
        }
    }

    private function ensureValidSecurityDomain(string $key, string $domain): void
    {
        if (! in_array($domain, ['auth', 'identity', 'session', 'transport', 'runtime', 'secrets'], true)) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security definition [%s] has unsupported domain [%s].',
                $key,
                $domain,
            ));
        }
    }

    private function ensureValidSecurityLevel(string $key, string $level, string $field): void
    {
        if (! in_array($level, ['required', 'recommended', 'optional', 'disallowed'], true)) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security definition [%s] has unsupported %s [%s].',
                $key,
                $field,
                $level,
            ));
        }
    }

    private function ensureValidSecurityEnforcementMode(string $key, string $mode, string $field): void
    {
        if (! in_array($mode, ['observe_only', 'package_owned', 'centrally_enforced'], true)) {
            throw new InvalidPackageDefinitionException(sprintf(
                'Security definition [%s] has unsupported %s [%s].',
                $key,
                $field,
                $mode,
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

    private function ensureValidHttpMiddlewareDefinition(PackageMetadata $metadata, HttpMiddlewareDefinition $httpMiddlewareDefinition): void
    {
        if ($httpMiddlewareDefinition->key === '') {
            throw new InvalidPackageDefinitionException('HTTP middleware definition key must not be empty.');
        }

        if ($httpMiddlewareDefinition->package !== $metadata->name) {
            throw new InvalidPackageDefinitionException(sprintf(
                'HTTP middleware definition [%s] must belong to package [%s].',
                $httpMiddlewareDefinition->key,
                $metadata->name,
            ));
        }

        if ($httpMiddlewareDefinition->middleware === '' || $httpMiddlewareDefinition->description === '') {
            throw new InvalidPackageDefinitionException(sprintf(
                'HTTP middleware definition [%s] must define middleware and description.',
                $httpMiddlewareDefinition->key,
            ));
        }

        if (! in_array($httpMiddlewareDefinition->kind, ['alias', 'web_prepend', 'web_append'], true)) {
            throw new InvalidPackageDefinitionException(sprintf(
                'HTTP middleware definition [%s] has unsupported kind [%s].',
                $httpMiddlewareDefinition->key,
                $httpMiddlewareDefinition->kind,
            ));
        }

        if ($httpMiddlewareDefinition->kind === 'alias' && ($httpMiddlewareDefinition->alias === null || $httpMiddlewareDefinition->alias === '')) {
            throw new InvalidPackageDefinitionException(sprintf(
                'HTTP middleware definition [%s] must define a non-empty alias for alias registrations.',
                $httpMiddlewareDefinition->key,
            ));
        }

        if ($httpMiddlewareDefinition->kind !== 'alias' && $httpMiddlewareDefinition->alias !== null) {
            throw new InvalidPackageDefinitionException(sprintf(
                'HTTP middleware definition [%s] may only define an alias when kind is [alias].',
                $httpMiddlewareDefinition->key,
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
