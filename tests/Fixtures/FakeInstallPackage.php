<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Install\InstallStep;

class FakeInstallPackage extends FakePlatformPackage implements DefinesInstallSteps
{
    /**
     * @param  array<int, InstallStep>  $steps
     */
    public function __construct(
        string $name = 'yezzmedia/laravel-install',
        private readonly array $steps = [],
        bool $enabled = true,
    ) {
        parent::__construct($name, $enabled);
    }

    public function installSteps(): array
    {
        return $this->steps;
    }
}
