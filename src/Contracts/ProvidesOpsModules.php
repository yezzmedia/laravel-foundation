<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\OpsModuleDefinition;

interface ProvidesOpsModules
{
    /**
     * @return array<int, OpsModuleDefinition>
     */
    public function opsModuleDefinitions(): array;
}
