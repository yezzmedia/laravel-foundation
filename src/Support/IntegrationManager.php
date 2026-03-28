<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;

/**
 * Answers simple integration questions against the normalized platform state.
 */
class IntegrationManager
{
    public function __construct(
        private readonly PackageRegistry $packages,
        private readonly FeatureRegistry $features,
    ) {}

    public function installed(string $package): bool
    {
        return $this->packages->has($package);
    }

    public function supports(string $feature): bool
    {
        return $this->features->has($feature);
    }
}
