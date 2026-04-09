<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\SecurityRequestDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class SecurityRequestRegistry
{
    /**
     * @var array<string, SecurityRequestDefinition>
     */
    private array $requests = [];

    private bool $sealed = false;

    public function register(SecurityRequestDefinition $request): void
    {
        $this->ensureNotSealed();

        if ($request->key === '') {
            throw new InvalidPackageDefinitionException('Security request key must not be empty.');
        }

        if (isset($this->requests[$request->key])) {
            throw new InvalidPackageDefinitionException(sprintf('Security request [%s] is already registered.', $request->key));
        }

        $this->requests[$request->key] = $request;
    }

    /**
     * @return Collection<int, SecurityRequestDefinition>
     */
    public function all(): Collection
    {
        return collect(array_values($this->requests));
    }

    /**
     * @return Collection<int, SecurityRequestDefinition>
     */
    public function forPackage(string $package): Collection
    {
        return $this->all()
            ->filter(static fn (SecurityRequestDefinition $request): bool => $request->package === $package)
            ->values();
    }

    public function seal(): void
    {
        $this->sealed = true;
    }

    private function ensureNotSealed(): void
    {
        if ($this->sealed) {
            throw new InvalidPackageDefinitionException('Security request registry is sealed.');
        }
    }
}
