<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Install\InstallStep;

class FakeInstallPackage extends FakePlatformPackage implements DefinesAuditEvents, DefinesInstallSteps
{
    /**
     * @param  array<int, InstallStep>  $steps
     * @param  array<int, AuditEventDefinition>  $auditEvents
     */
    public function __construct(
        string $name = 'yezzmedia/laravel-install',
        private readonly array $steps = [],
        private readonly array $auditEvents = [],
        bool $enabled = true,
    ) {
        parent::__construct($name, $enabled);
    }

    public function installSteps(): array
    {
        return $this->steps;
    }

    public function auditEventDefinitions(): array
    {
        return $this->auditEvents;
    }
}
