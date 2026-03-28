<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class OpsModuleRegistry
{
    /**
     * @var array<string, OpsModuleDefinition>
     */
    private array $modules = [];

    public function register(OpsModuleDefinition $module): void
    {
        if ($module->key === '') {
            throw new InvalidPackageDefinitionException('Ops module key must not be empty.');
        }

        if (isset($this->modules[$module->key])) {
            throw new InvalidPackageDefinitionException(sprintf('Ops module [%s] is already registered.', $module->key));
        }

        $this->modules[$module->key] = $module;
    }

    /**
     * @return Collection<int, OpsModuleDefinition>
     */
    public function all(): Collection
    {
        return collect(array_values($this->modules));
    }

    /**
     * @return Collection<int, OpsModuleDefinition>
     */
    public function forPackage(string $package): Collection
    {
        return $this->all()
            ->filter(static fn (OpsModuleDefinition $module): bool => $module->package === $package)
            ->values();
    }
}
