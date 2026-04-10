<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Registry;

use Illuminate\Support\Collection;
use YezzMedia\Foundation\Data\HttpMiddlewareDefinition;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class HttpMiddlewareRegistry
{
    /**
     * @var array<string, HttpMiddlewareDefinition>
     */
    private array $definitions = [];

    private bool $sealed = false;

    public function register(HttpMiddlewareDefinition $definition): void
    {
        $this->ensureNotSealed();

        if ($definition->key === '') {
            throw new InvalidPackageDefinitionException('HTTP middleware definition key must not be empty.');
        }

        if (isset($this->definitions[$definition->key])) {
            throw new InvalidPackageDefinitionException(sprintf('HTTP middleware definition [%s] is already registered.', $definition->key));
        }

        $this->definitions[$definition->key] = $definition;
    }

    /**
     * @return Collection<int, HttpMiddlewareDefinition>
     */
    public function all(): Collection
    {
        return collect(array_values($this->definitions));
    }

    /**
     * @return Collection<int, HttpMiddlewareDefinition>
     */
    public function forPackage(string $package): Collection
    {
        return $this->all()
            ->filter(static fn (HttpMiddlewareDefinition $definition): bool => $definition->package === $package)
            ->values();
    }

    public function has(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function seal(): void
    {
        $this->sealed = true;
    }

    private function ensureNotSealed(): void
    {
        if ($this->sealed) {
            throw new InvalidPackageDefinitionException('HTTP middleware registry is sealed.');
        }
    }
}
