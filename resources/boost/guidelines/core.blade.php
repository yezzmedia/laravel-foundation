## Laravel Foundation

`yezzmedia/laravel-foundation` is the shared platform core for Yezz Media package-based Laravel applications.
It provides the stable V1 contracts, registries, install and doctor workflows, console commands, and testing support that downstream platform packages build on.

### Core Patterns

- Register package runtime through a package descriptor that implements `YezzMedia\Foundation\Contracts\PlatformPackage`.
- Register package descriptors explicitly through `YezzMedia\Foundation\Support\PlatformPackageRegistrar`.
- Use `spatie/laravel-package-tools` and `PackageServiceProvider` for downstream package bootstrapping.
- Keep container bindings in `packageRegistered()` and explicit platform registration in `packageBooted()`.
- Use the approved capability contracts only when the package truly provides that surface:
  - `DefinesPermissions`
  - `DefinesAuditEvents`
  - `ProvidesDoctorChecks`
  - `ProvidesOpsModules`
  - `DefinesInstallSteps`
  - `RegistersFeatures`
  - `DefinesRateLimiters`
  - `DefinesCacheProfiles`

### Architecture Rules

- Treat the approved foundation surface as compatibility-sensitive.
- Do not invent new public foundation-facing contracts, DTO fields, commands, or extension points without plan and reference approval.
- Keep cross-package runtime contracts anchored on stable technical identifiers such as permission names, feature names, cache profile keys, and audit event keys.
- Do not move host-application behavior into package runtime code.
- Keep package registration explicit and auditable. Avoid hidden side effects in unrelated services.

### Testing Rules

- Prefer `YezzMedia\Foundation\Testing\FoundationTestCase` as the shared Testbench baseline for packages that consume foundation.
- Test package registration through the real foundation registries instead of bypassing them with convenience-only fixtures.
- When package boot order matters, exercise the real service provider and descriptor path.

### Skills

- Use the shipped `foundation-package-development` skill when building or changing packages that consume foundation.
- Use the shipped `foundation-core-development` skill when changing foundation internals such as registries, registrars, install or doctor managers, key factories, sealing, or event payload conventions.

### Example

@verbatim
<code-snippet name="Foundation Consumer Registration" lang="php">
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;

class AccessServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-access')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        // Bind package services here.
    }

    public function packageBooted(): void
    {
        $this->app->make(PlatformPackageRegistrar::class)
            ->register(new AccessPlatformPackage);
    }
}
</code-snippet>
@endverbatim
