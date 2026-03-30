# Foundation Core Runtime

This package builds one normalized runtime model for the platform.

## FoundationServiceProvider

`FoundationServiceProvider` is the composition root.

- Bind shared registries as singletons.
- Bind `PackageManifestLoader`, `PlatformPackageRegistrar`, `InstallManager`, `DoctorManager`, `IntegrationManager`, `CacheKeyFactory`, and `RateLimitKeyFactory`.
- Resolve config-backed defaults inside the provider.
- Seal registries and the manifest loader after boot when `foundation.registry.seal_after_boot` is enabled and the application is not running tests.

## Registrar Flow

`PlatformPackageRegistrar` is the only supported path for normalizing one package into foundation runtime state.

The flow is:

1. Read `metadata()` from the package descriptor.
2. Validate metadata against the descriptor class.
3. Register metadata in `PackageRegistry`.
4. Dispatch `PackageRegistered`.
5. Stop early for disabled packages.
6. Register enabled packages in `PackageManifestLoader`.
7. Validate and register declared features, permissions, and ops modules.
8. Validate audit events, rate limiters, and cache profiles.

## Disabled Package Rule

- Disabled packages still register package metadata.
- Disabled packages do not join manifest-driven runtime workflows.
- Disabled packages do not register features, permissions, or ops modules.

## Sealing Rule

Foundation seals its normalized state after provider boot in normal runtime.

- `PackageRegistry`
- `FeatureRegistry`
- `PermissionRegistry`
- `OpsModuleRegistry`
- `PackageManifestLoader`

Test environments may disable sealing so lightweight consumer tests can register additional fake packages after boot.
