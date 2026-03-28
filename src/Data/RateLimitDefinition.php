<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Data;

final readonly class RateLimitDefinition
{
    public function __construct(
        public string $key,
        public string $package,
        public string $description,
        public int $maxAttempts,
        public int $decaySeconds,
        public string $scope,
        public string $keyStrategy,
    ) {}
}
