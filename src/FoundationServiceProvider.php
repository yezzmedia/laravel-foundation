<?php

declare(strict_types=1);

namespace YezzMedia\Foundation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Console\WebsiteDoctorCommand;
use YezzMedia\Foundation\Console\WebsiteFeaturesCommand;
use YezzMedia\Foundation\Console\WebsiteInstallCommand;
use YezzMedia\Foundation\Console\WebsitePackagesCommand;
use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\CacheKeyFactory;
use YezzMedia\Foundation\Support\PackageManifestLoader;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;
use YezzMedia\Foundation\Support\RateLimitKeyFactory;

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
        $this->app->singleton(PermissionRegistry::class, static fn (): PermissionRegistry => new PermissionRegistry);
        $this->app->singleton(OpsModuleRegistry::class, static fn (): OpsModuleRegistry => new OpsModuleRegistry);
        $this->app->singleton(PackageManifestLoader::class, static fn (): PackageManifestLoader => new PackageManifestLoader);
        $this->app->singleton(CacheKeyFactory::class, static fn (): CacheKeyFactory => new CacheKeyFactory);
        $this->app->singleton(RateLimitKeyFactory::class, static fn (): RateLimitKeyFactory => new RateLimitKeyFactory);

        $this->app->singleton(PlatformPackageRegistrar::class, function (): PlatformPackageRegistrar {
            return new PlatformPackageRegistrar(
                packages: $this->app->make(PackageRegistry::class),
                features: $this->app->make(FeatureRegistry::class),
                permissions: $this->app->make(PermissionRegistry::class),
                opsModules: $this->app->make(OpsModuleRegistry::class),
                manifestLoader: $this->app->make(PackageManifestLoader::class),
            );
        });

        $this->app->singleton(InstallManager::class, function (): InstallManager {
            return new InstallManager(
                manifestLoader: $this->app->make(PackageManifestLoader::class),
            );
        });

        $this->app->singleton(DoctorManager::class, function (): DoctorManager {
            return new DoctorManager(
                manifestLoader: $this->app->make(PackageManifestLoader::class),
            );
        });
    }

    public function packageBooted(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            WebsiteInstallCommand::class,
            WebsiteDoctorCommand::class,
            WebsitePackagesCommand::class,
            WebsiteFeaturesCommand::class,
        ]);

        require_once __DIR__.'/../routes/console.php';
    }
}
