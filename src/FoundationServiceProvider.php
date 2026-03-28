<?php

declare(strict_types=1);

namespace YezzMedia\Foundation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Console\WebsiteDoctorCommand;
use YezzMedia\Foundation\Console\WebsiteFeaturesCommand;
use YezzMedia\Foundation\Console\WebsiteInstallCommand;
use YezzMedia\Foundation\Console\WebsitePackagesCommand;
use YezzMedia\Foundation\Contracts\ResolvesSiteContext;
use YezzMedia\Foundation\Data\SiteContext;
use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Install\InstallManager;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\Foundation\Support\CacheKeyFactory;
use YezzMedia\Foundation\Support\IntegrationManager;
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
        $this->app->singleton(CacheKeyFactory::class, function (): CacheKeyFactory {
            $prefix = config('foundation.cache.prefix', 'website');
            $separator = config('foundation.cache.separator', ':');

            return new CacheKeyFactory(
                prefix: is_string($prefix) ? $prefix : 'website',
                separator: is_string($separator) ? $separator : ':',
            );
        });
        $this->app->singleton(RateLimitKeyFactory::class, function (): RateLimitKeyFactory {
            $separator = config('foundation.rate_limits.separator', ':');

            return new RateLimitKeyFactory(
                separator: is_string($separator) ? $separator : ':',
            );
        });
        $this->app->singleton(IntegrationManager::class, function (): IntegrationManager {
            return new IntegrationManager(
                packages: $this->app->make(PackageRegistry::class),
                features: $this->app->make(FeatureRegistry::class),
            );
        });
        $this->app->singleton(ResolvesSiteContext::class, function (): ResolvesSiteContext {
            return new class implements ResolvesSiteContext
            {
                public function resolve(): SiteContext
                {
                    $locale = config('app.locale');

                    return new SiteContext(
                        environment: app()->environment(),
                        locale: is_string($locale) ? $locale : null,
                    );
                }
            };
        });

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
        $this->app->booted(function (): void {
            if (! (bool) config('foundation.registry.seal_after_boot', true) || $this->app->runningUnitTests()) {
                return;
            }

            $this->app->make(PackageRegistry::class)->seal();
            $this->app->make(FeatureRegistry::class)->seal();
            $this->app->make(PermissionRegistry::class)->seal();
            $this->app->make(OpsModuleRegistry::class)->seal();
            $this->app->make(PackageManifestLoader::class)->seal();
        });

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
