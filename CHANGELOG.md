# Changelog

All notable changes to `yezzmedia/laravel-foundation` will be documented in this file.

The format is based on Keep a Changelog and this package follows Semantic Versioning.

## [Unreleased]

### Added

- explicit install execution context through `InstallContext`
- `website:install --migrate` to allow install steps to run required migrations
- `website:install --refresh-publish` to allow install steps to refresh published resources intentionally
- self-registration for `yezzmedia/laravel-foundation` inside the package registry with priority `0`

### Changed

- install-step contracts now receive install context in both `shouldRun()` and `handle()`
- install results now report migration and publish-refresh intent in normalized context output
- installation failures now include the underlying step exception message

### Documentation

- documented the explicit install flags and the foundation self-registration behavior in the package README

## [0.1.0] - 2026-03-30

### Added

- shared platform bootstrap through `FoundationServiceProvider`
- normalized package registration through `PlatformPackageRegistrar`
- core registries:
  - `PackageRegistry`
  - `FeatureRegistry`
  - `PermissionRegistry`
  - `OpsModuleRegistry`
- manifest aggregation through `PackageManifestLoader`
- install orchestration through `InstallManager`
- doctor orchestration through `DoctorManager`
- reusable key factories:
  - `CacheKeyFactory`
  - `RateLimitKeyFactory`
- console commands:
  - `website:install`
  - `website:doctor`
  - `website:packages`
  - `website:features`
- stable platform contracts for package capabilities and runtime integration
- foundation lifecycle and diagnostic events
- reusable Testbench support through `FoundationTestCase` and testing concerns
- package-native Boost skills for foundation package work and foundation core work

### Changed

- established the approved V1 package registration and registry-driven runtime architecture for downstream platform packages

### Documentation

- documented the package-consumer and foundation-core workflows through shipped Boost skill references
