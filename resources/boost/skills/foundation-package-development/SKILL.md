---
name: foundation-package-development
description: "Build and maintain packages that consume yezzmedia/laravel-foundation. Activate when creating or changing PlatformPackage descriptors, PlatformPackageRegistrar integrations, foundation capability contracts, foundation-aligned service providers, registry-driven package tests, install or doctor surfaces, or other package code that depends on the approved foundation V1 architecture."
license: MIT
metadata:
  author: yezzmedia
---

# Foundation Package Development

## Documentation

Use `search-docs` for Laravel, Package Tools, Pest, and Boost details. Use the reference files in this skill for project-specific foundation patterns.

If the work changes `yezzmedia/laravel-foundation` internals themselves, switch to the `foundation-core-development` skill.

## When To Use This Skill

Activate this skill when working on a package that consumes `yezzmedia/laravel-foundation`, especially when:

- creating or changing a `PlatformPackage` descriptor
- registering a package through `PlatformPackageRegistrar`
- deciding which foundation capability contracts a package should implement
- building foundation-aware `PackageServiceProvider` bootstrap code
- adding install, doctor, feature, permission, audit-event, rate-limit, or cache-profile declarations
- writing package tests that must exercise the real foundation registration flow

## Working Rules

- Read the package plan and reference documents before changing public surface.
- Keep new package runtime code inside package-owned paths.
- Use `spatie/laravel-package-tools` with `PackageServiceProvider`.
- Put container bindings in `packageRegistered()`.
- Put explicit foundation registration in `packageBooted()`.
- Keep descriptors small and declarative.
- Implement a capability contract only when the package genuinely provides that capability.
- Treat approved public surface as compatibility-sensitive.
- Avoid inventing new public DTO fields, commands, or contracts unless the plan and reference are updated first.

## Standard Bootstrap Pattern

1. Build a package descriptor that implements `PlatformPackage`.
2. Add only the foundation capability contracts that the package truly needs.
3. Return stable technical identifiers from DTO declarations.
4. Bind package services in `packageRegistered()`.
5. Register the descriptor through `PlatformPackageRegistrar` in `packageBooted()`.
6. Verify registration through the real foundation registries in tests.

## Capability Contract Selection

- Use `RegistersFeatures` for feature declarations.
- Use `DefinesPermissions` for stable permission-name declarations.
- Use `DefinesAuditEvents` for package-owned audit event definitions.
- Use `ProvidesDoctorChecks` for package-owned operational diagnostics.
- Use `ProvidesOpsModules` for ops-module declarations.
- Use `DefinesInstallSteps` for install workflow steps.
- Use `DefinesRateLimiters` for normalized rate-limit definitions.
- Use `DefinesCacheProfiles` for explicit cache-profile definitions.

Do not add capability contracts for surfaces that are still empty unless that empty surface is already part of the approved package API.

## Testing Pattern

- Start from `YezzMedia\Foundation\Testing\FoundationTestCase` when building a consumer package test base.
- Prefer real provider boot and descriptor registration over manual registry mutation.
- Assert against `PackageRegistry`, `FeatureRegistry`, `PermissionRegistry`, and `OpsModuleRegistry` as appropriate.
- Keep tests close to production behavior. Avoid unrealistic shortcuts that bypass the foundation architecture.

## References

- Use [references/contracts.md](references/contracts.md) to choose the correct foundation capability contract.
- Use [references/bootstrap-pattern.md](references/bootstrap-pattern.md) for the standard service-provider and descriptor pattern.
- Use [references/testing.md](references/testing.md) for Testbench and registry-based test expectations.
- Use [references/checklist.md](references/checklist.md) before finalizing package work.
- Use the `foundation-core-development` skill instead when changing foundation registries, managers, key factories, or event conventions.

## Example

```php
final class AccessPlatformPackage implements PlatformPackage, DefinesAuditEvents
{
    public function metadata(): PackageMetadata
    {
        return new PackageMetadata(
            name: 'yezzmedia/laravel-access',
            vendor: 'yezzmedia',
            description: 'Persistent roles and permissions package for the Yezz Media Laravel website platform.',
            packageClass: self::class,
        );
    }

    public function auditEventDefinitions(): array
    {
        return [
            new AuditEventDefinition(
                key: 'access.permissions.synchronized',
                package: 'yezzmedia/laravel-access',
                action: 'synchronized',
                subjectType: 'permission_set',
                description: 'Access permissions were synchronized.',
            ),
        ];
    }
}
```

## Common Pitfalls

- Registering package descriptors indirectly instead of through `PlatformPackageRegistrar`
- Putting package registration in `register()` or unrelated services
- Treating role names or other human-facing labels as cross-package contracts
- Adding new public surface without plan and reference alignment
- Testing declarations without exercising the real provider and registry path
