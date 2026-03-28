<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class OpsModuleDefinition
{
    public function __construct(
        public string $key,
        public string $package,
        public string $label,
        public string $type,
        public ?string $permissionHint = null,
    ) {}
}
