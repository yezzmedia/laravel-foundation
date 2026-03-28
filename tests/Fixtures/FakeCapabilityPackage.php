<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesCacheProfiles;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\DefinesRateLimiters;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\CacheProfile;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Data\RateLimitDefinition;

class FakeCapabilityPackage extends FakePlatformPackage implements DefinesAuditEvents, DefinesCacheProfiles, DefinesPermissions, DefinesRateLimiters, ProvidesOpsModules
{
    /**
     * @param  array<int, PermissionDefinition>  $permissions
     * @param  array<int, OpsModuleDefinition>  $opsModules
     * @param  array<int, AuditEventDefinition>  $auditEvents
     * @param  array<int, RateLimitDefinition>  $rateLimiters
     * @param  array<int, CacheProfile>  $cacheProfiles
     */
    public function __construct(
        string $name = 'yezzmedia/laravel-ops',
        private readonly array $permissions = [],
        private readonly array $opsModules = [],
        private readonly array $auditEvents = [],
        private readonly array $rateLimiters = [],
        private readonly array $cacheProfiles = [],
        bool $enabled = true,
    ) {
        parent::__construct($name, $enabled);
    }

    public function auditEventDefinitions(): array
    {
        return $this->auditEvents;
    }

    public function cacheProfiles(): array
    {
        return $this->cacheProfiles;
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
}
