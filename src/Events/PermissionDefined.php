<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

final readonly class PermissionDefined
{
    public function __construct(
        public string $permissionName,
        public string $packageName,
    ) {}
}
