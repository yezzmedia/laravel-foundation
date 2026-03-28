<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Data\FeatureDefinition;

class FakeFeaturePackage extends FakePlatformPackage implements RegistersFeatures
{
    public function __construct(
        string $name = 'yezzmedia/laravel-content',
        private readonly string $featureName = 'content.pages',
        private readonly string $featurePackage = 'yezzmedia/laravel-content',
        bool $enabled = true,
    ) {
        parent::__construct($name, $enabled);
    }

    public function featureDefinitions(): array
    {
        return [
            new FeatureDefinition(
                name: $this->featureName,
                package: $this->featurePackage,
                label: 'Content pages',
                description: 'Registers content page support.',
            ),
        ];
    }
}
