<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Testing\Concerns;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Registry\FeatureRegistry;

trait InteractsWithFeatureRegistry
{
    public function featureRegistry(): FeatureRegistry
    {
        return app(FeatureRegistry::class);
    }

    /**
     * @return Collection<int, FeatureDefinition>
     */
    public function registeredFeatures(): Collection
    {
        return $this->featureRegistry()->all();
    }

    public function assertRegisteredFeature(string $name): void
    {
        Assert::assertTrue(
            $this->featureRegistry()->has($name),
            sprintf('Failed to assert that feature [%s] is registered.', $name),
        );
    }
}
