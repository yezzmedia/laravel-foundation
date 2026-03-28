<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Exceptions\InvalidPackageDefinitionException;

class PackageManifestLoader
{
    /**
     * @var array<string, PlatformPackage>
     */
    private array $packages = [];

    private bool $sealed = false;

    public function register(PlatformPackage $package): void
    {
        $this->ensureNotSealed();

        $this->packages[$package->metadata()->name] = $package;
    }

    /**
     * @return array<int, PlatformPackage>
     */
    public function packages(): array
    {
        return array_values($this->packages);
    }

    public function seal(): void
    {
        $this->sealed = true;
    }

    private function ensureNotSealed(): void
    {
        if ($this->sealed) {
            throw new InvalidPackageDefinitionException('Package manifest loader is sealed.');
        }
    }
}
