<?php

declare(strict_types=1);

namespace YezzMedia\Foundation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

class FoundationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-foundation')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PackageRegistry::class, static fn (): PackageRegistry => new PackageRegistry);
        $this->app->singleton(FeatureRegistry::class, static fn (): FeatureRegistry => new FeatureRegistry);

        $this->app->singleton(PlatformPackageRegistrar::class, function (): PlatformPackageRegistrar {
            return new PlatformPackageRegistrar(
                packages: $this->app->make(PackageRegistry::class),
                features: $this->app->make(FeatureRegistry::class),
            );
        });
    }

    public function packageBooted(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        require_once __DIR__.'/../routes/console.php';
    }
}
