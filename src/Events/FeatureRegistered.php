<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

final readonly class FeatureRegistered
{
    public function __construct(
        public string $featureName,
        public string $packageName,
    ) {}
}
