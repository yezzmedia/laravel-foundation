---
name: foundation-core-development
description: "Maintain the internal runtime of yezzmedia/laravel-foundation. Activate when changing FoundationServiceProvider bindings, PlatformPackageRegistrar validation, registries, PackageManifestLoader, install or doctor managers, key factories, sealing behavior, foundation events, or other compatibility-sensitive core runtime conventions."
license: MIT
metadata:
  author: yezzmedia
---

# Foundation Core Development

## Documentation

Use `search-docs` for Laravel, Package Tools, and Boost details. Use the reference files in this skill for project-specific foundation runtime rules.

## When To Use This Skill

Activate this skill when changing `yezzmedia/laravel-foundation` internals, especially:

- `FoundationServiceProvider`
- `PlatformPackageRegistrar`
- `PackageManifestLoader`
- `PackageRegistry`, `FeatureRegistry`, `PermissionRegistry`, `OpsModuleRegistry`
- `InstallManager` and `DoctorManager`
- `CacheKeyFactory` and `RateLimitKeyFactory`
- foundation events, DTO invariants, and runtime validation rules
- sealing, manifest loading, or compatibility-sensitive runtime conventions

## Core Principles

- Treat the foundation package as compatibility-sensitive infrastructure.
- Prefer the smallest correct internal change.
- Keep runtime contracts explicit, deterministic, and auditable.
- Do not add new public contracts, DTO fields, commands, or config groups without plan and reference alignment.
- Keep host-application behavior out of foundation runtime code.
- Preserve the explicit descriptor-registration model through `PlatformPackageRegistrar`.

## Runtime Model

1. `FoundationServiceProvider` binds the shared registries, managers, factories, and context services.
2. Consumer packages register one descriptor through `PlatformPackageRegistrar`.
3. The registrar validates declarations and writes normalized state into the registries.
4. Enabled packages are added to the manifest loader for install and doctor aggregation.
5. Registries and the manifest loader are sealed after boot in non-test runtime when sealing is enabled.

## High-Risk Areas

- descriptor validation rules in `PlatformPackageRegistrar`
- registry insertion and sealing behavior
- install-step ordering, failure handling, and result shaping
- doctor-check ordering, allowed statuses, and blocking-failure semantics
- event payload shapes
- key normalization and escaping rules in cache and rate-limit factories

## Change Rules

- Preserve the rule that disabled packages register package metadata but do not join runtime capability aggregation.
- Keep validation close to the registrar and managers that own it.
- Keep event payloads technical and small.
- Keep install and doctor logic in managers, not in commands.
- Keep key factories deterministic and separator-safe.
- Keep sealing predictable and easy to override in tests.

## Testing Pattern

- Exercise the real service provider path whenever boot order or container wiring matters.
- Test validation failures explicitly with the real registrar or manager.
- Add focused unit tests for result shapes, ordering, and normalization behavior.
- Keep tests aligned with runtime behavior instead of introducing convenience-only bypasses.

## References

- Use [references/core-runtime.md](references/core-runtime.md) for the provider, registrar, manifest, and sealing model.
- Use [references/install-doctor.md](references/install-doctor.md) for install and doctor manager behavior.
- Use [references/runtime-conventions.md](references/runtime-conventions.md) for validation, event, and key-factory rules.
- Use [references/checklist.md](references/checklist.md) before finalizing foundation-core changes.

## Example

```php
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
}
```

## Common Pitfalls

- letting disabled packages leak into manifest-driven workflows
- changing event payloads casually
- moving validation away from the registrar or owning manager
- bypassing sealing semantics in production code
- mixing host-level assumptions into foundation services
