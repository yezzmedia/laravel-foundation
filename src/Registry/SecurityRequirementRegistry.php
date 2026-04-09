<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\SecurityRequirementDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class SecurityRequirementRegistry
{
    /**
     * @var array<string, SecurityRequirementDefinition>
     */
    private array $requirements = [];

    private bool $sealed = false;

    public function register(SecurityRequirementDefinition $requirement): void
    {
        $this->ensureNotSealed();

        if ($requirement->key === '') {
            throw new InvalidPackageDefinitionException('Security requirement key must not be empty.');
        }

        if (isset($this->requirements[$requirement->key])) {
            throw new InvalidPackageDefinitionException(sprintf('Security requirement [%s] is already registered.', $requirement->key));
        }

        $this->requirements[$requirement->key] = $requirement;
    }

    /**
     * @return Collection<int, SecurityRequirementDefinition>
     */
    public function all(): Collection
    {
        return collect(array_values($this->requirements));
    }

    /**
     * @return Collection<int, SecurityRequirementDefinition>
     */
    public function forPackage(string $package): Collection
    {
        return $this->all()
            ->filter(static fn (SecurityRequirementDefinition $requirement): bool => $requirement->package === $package)
            ->values();
    }

    public function seal(): void
    {
        $this->sealed = true;
    }

    private function ensureNotSealed(): void
    {
        if ($this->sealed) {
            throw new InvalidPackageDefinitionException('Security requirement registry is sealed.');
        }
    }
}
