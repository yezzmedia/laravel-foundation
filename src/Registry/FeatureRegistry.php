<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class FeatureRegistry
{
    /**
     * @var array<string, FeatureDefinition>
     */
    private array $features = [];

    private bool $sealed = false;

    public function register(FeatureDefinition $feature): void
    {
        $this->ensureNotSealed();

        if ($feature->name === '') {
            throw new InvalidPackageDefinitionException('Feature name must not be empty.');
        }

        if (isset($this->features[$feature->name])) {
            throw new InvalidPackageDefinitionException(sprintf('Feature [%s] is already registered.', $feature->name));
        }

        $this->features[$feature->name] = $feature;
    }

    /**
     * @return Collection<int, FeatureDefinition>
     */
    public function all(): Collection
    {
        return collect(array_values($this->features));
    }

    /**
     * @return Collection<int, FeatureDefinition>
     */
    public function forPackage(string $package): Collection
    {
        return $this->all()
            ->filter(static fn (FeatureDefinition $feature): bool => $feature->package === $package)
            ->values();
    }

    public function has(string $feature): bool
    {
        return isset($this->features[$feature]);
    }

    public function seal(): void
    {
        $this->sealed = true;
    }

    private function ensureNotSealed(): void
    {
        if ($this->sealed) {
            throw new InvalidPackageDefinitionException('Feature registry is sealed.');
        }
    }
}
