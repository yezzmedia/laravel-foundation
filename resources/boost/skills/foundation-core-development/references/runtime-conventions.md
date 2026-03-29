# Runtime Conventions

Foundation internals should keep runtime state deterministic and easy to diagnose.

## Validation Placement

- Validate descriptor-owned declarations in `PlatformPackageRegistrar`.
- Validate install steps in `InstallManager`.
- Validate doctor checks and doctor results in `DoctorManager`.
- Throw `InvalidPackageDefinitionException` for invalid package-owned declarations.

## Event Payload Rules

- Keep foundation events technical and small.
- Prefer stable identifiers and counts over large DTO payloads.
- `PackageRegistered`, `FeatureRegistered`, `PermissionDefined`, and `OpsModuleDefined` should expose only the technical identifiers required for listeners.
- `WebsiteInstalled` should carry status, executed count, failed count, and optional context.
- `DoctorChecksCompleted` should carry only the summary counts.

## Key Factory Rules

`CacheKeyFactory` and `RateLimitKeyFactory` build deterministic keys.

- Normalize and trim all structural segments.
- Reject empty structural values.
- Escape `%` first.
- Escape separator characters so dynamic values cannot change key structure.
- Keep output deterministic for repeated identical input.

## Registry And Manifest Rules

- Registries reject empty identifiers.
- Registries reject duplicate registration.
- Registries and the manifest loader throw when modified after sealing.
- Manifest order is controlled by the owning manager, not by the manifest loader itself.

## Compatibility Rules

- Treat DTO fields, event payloads, manager return shapes, and config keys as public API once approved.
- Prefer additive internal helpers over public-surface expansion.
- If a change affects public expectations, align plan and reference first.
