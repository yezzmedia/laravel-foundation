<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

/**
 * Carries explicit runtime intent for one install execution.
 */
final readonly class InstallContext
{
    /**
     * @param  array<int, string>  $auditPackages
     */
    public function __construct(
        public bool $allowMigrations = false,
        public bool $refreshPublishedResources = false,
        public bool $configureAccessAudit = false,
        public bool $configureAudit = false,
        public array $auditPackages = [],
    ) {}

    public function configuresAudit(): bool
    {
        return $this->configureAudit || $this->configureAccessAudit;
    }

    public function shouldConfigureAuditFor(string $package): bool
    {
        return in_array($package, $this->selectedAuditPackages(), true);
    }

    /**
     * @return array<int, string>
     */
    public function selectedAuditPackages(): array
    {
        $packages = array_values(array_filter(
            $this->auditPackages,
            static fn (mixed $package): bool => is_string($package) && $package !== '',
        ));

        if ($this->configureAccessAudit) {
            $packages[] = 'yezzmedia/laravel-access';
        }

        return array_values(array_unique($packages));
    }
}
