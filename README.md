# Laravel Foundation

`yezzmedia/laravel-foundation` is the shared platform core for Yezz Media package-based Laravel applications.

It provides the stable runtime that downstream platform packages build on: package registration, feature and permission registries, install and doctor orchestration, cache and rate-limit key factories, platform console commands, and reusable Testbench support for consumer packages.

## Version

Current release: `0.1.0`

## Requirements

- PHP `^8.3`
- Laravel `^13.0` support and console components
- `spatie/laravel-package-tools ^1.93`

## Installation

Install the package in the consuming Laravel application:

```bash
composer require yezzmedia/laravel-foundation
```

The service provider is auto-discovered.

## Configuration

Publish the package config when you need to override defaults:

```bash
php artisan vendor:publish --provider="YezzMedia\Foundation\FoundationServiceProvider" --tag="config"
```

Default configuration:

```php
return [
    'registry' => [
        'seal_after_boot' => true,
    ],

    'rate_limits' => [
        'separator' => ':',
    ],

    'cache' => [
        'prefix' => 'website',
        'separator' => ':',
    ],
];
```

## What The Package Provides

### Foundation runtime bootstrapping

`FoundationServiceProvider` registers the shared platform services that downstream packages consume.

It binds:

- `PackageRegistry`
- `FeatureRegistry`
- `PermissionRegistry`
- `OpsModuleRegistry`
- `PackageManifestLoader`
- `PlatformPackageRegistrar`
- `InstallManager`
- `DoctorManager`
- `CacheKeyFactory`
- `RateLimitKeyFactory`
- `IntegrationManager`
- `ResolvesSiteContext`

After boot, foundation seals the registries by default so the normalized package state stays read-only during ordinary runtime flows.

### Package registration

Downstream packages integrate with foundation by registering a descriptor that implements `PlatformPackage`.

Foundation normalizes that descriptor through `PlatformPackageRegistrar` into the shared registries.

Supported capability contracts include:

- `RegistersFeatures`
- `DefinesPermissions`
- `DefinesAuditEvents`
- `ProvidesDoctorChecks`
- `ProvidesOpsModules`
- `DefinesInstallSteps`
- `DefinesRateLimiters`
- `DefinesCacheProfiles`

Example registration pattern:

```php
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

final class ExampleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('example-package');
    }

    public function packageBooted(): void
    {
        app(PlatformPackageRegistrar::class)->register(new ExamplePlatformPackage);
    }
}
```

### Registries and normalized state

Foundation provides central registries for the normalized platform surface:

- packages
- features
- permissions
- ops modules

These registries are what install, doctor, package listing, and feature listing work against.

### Install and doctor workflows

`InstallManager` orchestrates declared install steps across registered platform packages.

- runs package install steps in deterministic order
- supports filtering with explicit package names
- stops on the first blocking failure
- reports executed, failed, and skipped steps through `InstallResult`

`DoctorManager` aggregates declared doctor checks across registered platform packages.

- validates check metadata and result shape
- supports normalized statuses:
  - `passed`
  - `warning`
  - `failed`
  - `skipped`
- separates blocking failures from non-blocking output

### Console commands

Foundation exposes these platform commands:

```bash
php artisan website:install
php artisan website:doctor
php artisan website:packages
php artisan website:features
```

- `website:install` runs declared install steps
- `website:doctor` reports declared doctor checks
- `website:packages` lists registered platform packages
- `website:features` lists registered platform features

### Key factories

Foundation ships reusable key factories for downstream packages:

- `CacheKeyFactory`
- `RateLimitKeyFactory`

These keep cross-package technical identifiers consistent.

### Events

Foundation emits platform lifecycle events such as:

- `PackageRegistered`
- `FeatureRegistered`
- `PermissionDefined`
- `OpsModuleDefined`
- `WebsiteInstalled`
- `DoctorChecksCompleted`

### Testing support

Foundation exposes a reusable Testbench base and helper concerns for consumer package tests:

- `YezzMedia\Foundation\Testing\FoundationTestCase`
- `YezzMedia\Foundation\Testing\Concerns\InteractsWithPackageRegistry`
- `YezzMedia\Foundation\Testing\Concerns\InteractsWithFeatureRegistry`
- `YezzMedia\Foundation\Testing\Concerns\InteractsWithDoctorManager`
- `YezzMedia\Foundation\Testing\Concerns\InteractsWithInstallManager`

These helpers keep consumer package tests on the real foundation registration path while reducing repetitive setup.

## Consumer package usage

Foundation is intended to be consumed by platform packages, not by feature code that wants to bypass package registration.

Use it when building packages that need to:

- declare stable permissions
- register features
- expose install steps or doctor checks
- participate in shared platform ops workflows
- test package registration through a real Testbench baseline

## Boost skills

The package ships package-native Boost skills for both sides of foundation work:

- `resources/boost/skills/foundation-package-development/`
- `resources/boost/skills/foundation-core-development/`

These document the approved package-consumer and core-runtime workflows for the foundation architecture.

## Development

Available package scripts:

```bash
composer test
composer analyse
composer format
```

## License

MIT
