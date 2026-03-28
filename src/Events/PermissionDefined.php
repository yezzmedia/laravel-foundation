<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

use YezzMedia\Foundation\Data\PermissionDefinition;

final readonly class PermissionDefined
{
    public function __construct(public PermissionDefinition $permission) {}
}
