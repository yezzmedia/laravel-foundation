<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\SecurityRequestDefinition;

interface DefinesSecurityRequests
{
    /**
     * @return array<int, SecurityRequestDefinition>
     */
    public function securityRequestDefinitions(): array;
}
