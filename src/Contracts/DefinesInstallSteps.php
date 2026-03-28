<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Install\InstallStep;

interface DefinesInstallSteps
{
    /**
     * @return array<int, InstallStep>
     */
    public function installSteps(): array;
}
