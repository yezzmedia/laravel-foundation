<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Events;

use YezzMedia\Foundation\Data\FeatureDefinition;

final readonly class FeatureRegistered
{
    public function __construct(public FeatureDefinition $feature) {}
}
