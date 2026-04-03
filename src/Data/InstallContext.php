<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

/**
 * Carries explicit runtime intent for one install execution.
 */
final readonly class InstallContext
{
    public function __construct(
        public bool $allowMigrations = false,
        public bool $refreshPublishedResources = false,
        public bool $configureAccessAudit = false,
    ) {}
}
