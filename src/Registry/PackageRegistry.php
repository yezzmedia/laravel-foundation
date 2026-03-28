<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class PackageRegistry
{
    /**
     * @var array<string, PackageMetadata>
     */
    private array $packages = [];

    private bool $sealed = false;

    public function register(PackageMetadata $package): void
    {
        $this->ensureNotSealed();

        if ($package->name === '') {
            throw new InvalidPackageDefinitionException('Package name must not be empty.');
        }

        if (isset($this->packages[$package->name])) {
            throw new InvalidPackageDefinitionException(sprintf('Package [%s] is already registered.', $package->name));
        }

        $this->packages[$package->name] = $package;
    }

    /**
     * @return Collection<int, PackageMetadata>
     */
    public function all(): Collection
    {
        return collect(array_values($this->packages));
    }

    public function find(string $name): ?PackageMetadata
    {
        return $this->packages[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->packages[$name]);
    }

    public function seal(): void
    {
        $this->sealed = true;
    }

    private function ensureNotSealed(): void
    {
        if ($this->sealed) {
            throw new InvalidPackageDefinitionException('Package registry is sealed.');
        }
    }
}
