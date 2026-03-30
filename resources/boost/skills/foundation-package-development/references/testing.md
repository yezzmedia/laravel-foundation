# Testing Pattern

Foundation consumers should test the real registration flow, not a shortcut around it.

## Base Test Case

- Prefer `YezzMedia\Foundation\Testing\FoundationTestCase` as the shared Testbench base for downstream packages.
- Register the consumer package service provider in the package-specific test case.
- Keep test environment wiring close to production defaults unless a test requires explicit overrides.

## What To Verify

- package bindings resolve from the container
- package configuration merges correctly
- package descriptor is registered in `PackageRegistry`
- declared features, permissions, ops modules, or audit events appear through the real foundation registries
- boot-time behavior happens only through the intended provider path

## Preferred Assertions

- Assert registry state through `PackageRegistry`, `FeatureRegistry`, `PermissionRegistry`, and `OpsModuleRegistry`.
- Instantiate the real service provider path rather than mutating registries manually.
- Verify package-owned defaults before adding deeper integration slices.

## Avoid

- fake helper setups that bypass provider boot
- tests that mutate foundation registries directly unless the registry itself is under test
- hiding architectural coupling in fixtures that do not exist in runtime
