<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class PermissionRegistry
{
    /**
     * @var array<string, PermissionDefinition>
     */
    private array $permissions = [];

    public function register(PermissionDefinition $permission): void
    {
        if ($permission->name === '') {
            throw new InvalidPackageDefinitionException('Permission name must not be empty.');
        }

        if (isset($this->permissions[$permission->name])) {
            throw new InvalidPackageDefinitionException(sprintf('Permission [%s] is already registered.', $permission->name));
        }

        $this->permissions[$permission->name] = $permission;
    }

    /**
     * @return Collection<int, PermissionDefinition>
     */
    public function all(): Collection
    {
        return collect(array_values($this->permissions));
    }

    /**
     * @return Collection<int, PermissionDefinition>
     */
    public function forPackage(string $package): Collection
    {
        return $this->all()
            ->filter(static fn (PermissionDefinition $permission): bool => $permission->package === $package)
            ->values();
    }
}
