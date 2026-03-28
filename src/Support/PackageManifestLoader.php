<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Support;

use YezzMedia\Foundation\Contracts\PlatformPackage;

class PackageManifestLoader
{
    /**
     * @var array<string, PlatformPackage>
     */
    private array $packages = [];

    public function register(PlatformPackage $package): void
    {
        $this->packages[$package->metadata()->name] = $package;
    }

    /**
     * @return array<int, PlatformPackage>
     */
    public function packages(): array
    {
        return array_values($this->packages);
    }
}
