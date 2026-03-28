<?php

declare(strict_types=1);

namespace YezzMedia\Foundation\Testing\Concerns;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

trait InteractsWithPackageRegistry
{
    public function packageRegistry(): PackageRegistry
    {
        return app(PackageRegistry::class);
    }

    public function registerPackage(PlatformPackage $package): PlatformPackage
    {
        app(PlatformPackageRegistrar::class)->register($package);

        return $package;
    }

    /**
     * @return Collection<int, PackageMetadata>
     */
    public function registeredPackages(): Collection
    {
        return $this->packageRegistry()->all();
    }

    public function assertRegisteredPackage(string $name): void
    {
        Assert::assertTrue(
            $this->packageRegistry()->has($name),
            sprintf('Failed to assert that package [%s] is registered.', $name),
        );
    }
}
