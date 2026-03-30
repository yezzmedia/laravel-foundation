# Foundation Contracts

Use the smallest approved contract set that matches the package's real responsibilities.

## Core Descriptor

- `YezzMedia\Foundation\Contracts\PlatformPackage`
  - Required package descriptor root.
  - Return `PackageMetadata` only.

## Capability Contracts

- `RegistersFeatures`
  - Use when the package defines feature flags or feature registrations for the platform.
- `DefinesPermissions`
  - Use when the package declares stable permission names for downstream persistence.
- `DefinesAuditEvents`
  - Use when the package owns normalized audit event definitions.
- `ProvidesDoctorChecks`
  - Use when the package contributes doctor diagnostics.
- `ProvidesOpsModules`
  - Use when the package contributes ops-module declarations.
- `DefinesInstallSteps`
  - Use when the package provides install workflow steps.
- `DefinesRateLimiters`
  - Use when the package provides normalized rate-limit definitions.
- `DefinesCacheProfiles`
  - Use when the package provides explicit cache-profile definitions.

## Selection Rules

- Prefer the narrowest contract set that matches the approved package surface.
- Do not implement a contract just because a package might need it later.
- Keep declarations declarative. Runtime logic belongs in package-owned services, not DTO declarations.
- If a proposed surface is not in the approved plan and reference, stop and align those first.

## Common Mistakes

- Adding `DefinesPermissions` when the package does not own permission declarations.
- Using `ProvidesOpsModules` for package-owned admin UI ideas that are not yet approved.
- Expanding DTOs instead of adding package-owned runtime services.
