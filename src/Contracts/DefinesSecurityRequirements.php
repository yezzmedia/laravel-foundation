<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\SecurityRequirementDefinition;

interface DefinesSecurityRequirements
{
    /**
     * @return array<int, SecurityRequirementDefinition>
     */
    public function securityRequirementDefinitions(): array;
}
