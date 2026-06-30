# Changelog

All notable changes to `yezzmedia/laravel-foundation` will be documented in this file.

The format is based on Keep a Changelog and this package follows Semantic Versioning.

## [Unreleased]

### Added

- explicit install execution context through `InstallContext`
- `website:install --migrate` to allow install steps to run required migrations
- `website:install --refresh-publish` to allow install steps to refresh published resources intentionally
- foundation HTTP middleware declaration and bridge surface through:
  - `DefinesHttpMiddleware`
  - `HttpMiddlewareDefinition`
  - `HttpMiddlewareRegistry`
  - `HttpMiddlewareResolver`
  - `FoundationHttpMiddlewareBridge`
- `ConfigureHttpMiddlewareBridgeInstallStep` for explicitly wiring the host bootstrap to the foundation middleware bridge
- `FoundationHttpMiddlewareBridgeConfiguredCheck` for verifying the host bootstrap bridge wiring during diagnostics
- self-registration for `yezzmedia/laravel-foundation` inside the package registry with priority `0`
- `website:install --configure-audit` with package selection through `--audit-package=*`
- `AuditInstallStep` for package-owned audit persistence setup steps
- security-governance declaration contracts:
  - `DefinesSecurityRequests`
  - `DefinesSecurityRequirements`
- security-governance DTOs:
  - `SecurityRequestDefinition`
  - `SecurityRequirementDefinition`
- central registries:
  - `SecurityRequestRegistry`
  - `SecurityRequirementRegistry`

### Changed

- install-step contracts now receive install context in both `shouldRun()` and `handle()`
- install results now report migration and publish-refresh intent in normalized context output
- install orchestration now supports explicit host bootstrap patching for the foundation HTTP middleware bridge
- installation failures now include the underlying step exception message
- install orchestration now distinguishes audit-only package setup from ordinary install flows
- `--configure-access-audit` is now a deprecated alias for `--configure-audit --audit-package=yezzmedia/laravel-access`
- packages with custom install steps must update older no-context method signatures to the current `InstallContext`-aware contract
- `PlatformPackageRegistrar` now validates and registers package-declared security requests and security requirements
- foundation runtime now binds and seals the security-governance registries alongside the existing normalized package registries

### Documentation

- documented the explicit install flags and the foundation self-registration behavior in the package README
- documented the implemented `website:install --configure-audit` and `--audit-package=*` flow, including the deprecation path for `--configure-access-audit`
- added a migration note in the README for consumer packages that still implement the pre-`InstallContext` install-step signatures
- documented the new security-governance declaration surface, DTOs, registries, and validation vocabulary in the package README
- documented the foundation HTTP middleware declaration surface, host bridge install step, and bridge doctor check in the package README

## [0.2.0] - 2026-06-30

### Changed

- Bumped minimum `yezzmedia/laravel-foundation` dependency to `^0.2`

## [0.1.1] - 2026-04-12

### Fixed

- shipped the security-governance and HTTP middleware declaration contracts that downstream `0.1.x` packages already rely on at runtime
- shipped the supporting security registries and HTTP middleware registry runtime that package discovery now expects during bootstrap

### Documentation

- documented the compatibility hotfix release for downstream ops package consumers

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
