<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class FeatureDefinition
{
    public function __construct(
        public string $name,
        public string $package,
        public string $label,
        public ?string $description = null,
    ) {}
}
