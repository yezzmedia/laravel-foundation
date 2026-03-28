<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

use YezzMedia\Foundation\Data\OpsModuleDefinition;

final readonly class OpsModuleDefined
{
    public function __construct(public OpsModuleDefinition $module) {}
}
