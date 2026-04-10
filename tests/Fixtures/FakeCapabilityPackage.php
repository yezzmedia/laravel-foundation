<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesCacheProfiles;
use YezzMedia\Foundation\Contracts\DefinesHttpMiddleware;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\DefinesRateLimiters;
use YezzMedia\Foundation\Contracts\DefinesSecurityRequests;
use YezzMedia\Foundation\Contracts\DefinesSecurityRequirements;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\CacheProfile;
use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Data\RateLimitDefinition;
use YezzMedia\Foundation\Data\SecurityRequestDefinition;
use YezzMedia\Foundation\Data\SecurityRequirementDefinition;

class FakeCapabilityPackage extends FakePlatformPackage implements DefinesAuditEvents, DefinesCacheProfiles, DefinesHttpMiddleware, DefinesPermissions, DefinesRateLimiters, DefinesSecurityRequests, DefinesSecurityRequirements, ProvidesOpsModules
{
    /**
     * @param  array<int, PermissionDefinition>  $permissions
     * @param  array<int, OpsModuleDefinition>  $opsModules
     * @param  array<int, AuditEventDefinition>  $auditEvents
     * @param  array<int, RateLimitDefinition>  $rateLimiters
     * @param  array<int, CacheProfile>  $cacheProfiles
     * @param  array<int, HttpMiddlewareDefinition>  $httpMiddleware
     * @param  array<int, SecurityRequestDefinition>  $securityRequests
     * @param  array<int, SecurityRequirementDefinition>  $securityRequirements
     */
    public function __construct(
        string $name = 'yezzmedia/laravel-ops',
        private readonly array $permissions = [],
        private readonly array $opsModules = [],
        private readonly array $auditEvents = [],
        private readonly array $rateLimiters = [],
        private readonly array $cacheProfiles = [],
        private readonly array $httpMiddleware = [],
        private readonly array $securityRequests = [],
        private readonly array $securityRequirements = [],
        bool $enabled = true,
        int $priority = 10,
    ) {
        parent::__construct($name, $enabled, $priority);
    }

    public function auditEventDefinitions(): array
    {
        return $this->auditEvents;
    }

    public function cacheProfiles(): array
    {
        return $this->cacheProfiles;
    }

    public function httpMiddlewareDefinitions(): array
    {
        return $this->httpMiddleware;
    }

    public function opsModuleDefinitions(): array
    {
        return $this->opsModules;
    }

    public function permissionDefinitions(): array
    {
        return $this->permissions;
    }

    public function rateLimitDefinitions(): array
    {
        return $this->rateLimiters;
    }

    public function securityRequestDefinitions(): array
    {
        return $this->securityRequests;
    }

    public function securityRequirementDefinitions(): array
    {
        return $this->securityRequirements;
    }
}
