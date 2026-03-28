<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\PermissionDefinition;

interface DefinesPermissions
{
    /**
     * @return array<int, PermissionDefinition>
     */
    public function permissionDefinitions(): array;
}
