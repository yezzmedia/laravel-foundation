<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

final readonly class OpsModuleDefined
{
    public function __construct(
        public string $moduleKey,
        public string $packageName,
    ) {}
}
