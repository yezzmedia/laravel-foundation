<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Contracts;

use YezzMedia\Foundation\Data\FeatureDefinition;

interface RegistersFeatures
{
    /**
     * @return array<int, FeatureDefinition>
     */
    public function featureDefinitions(): array;
}
