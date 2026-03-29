# Foundation Consumer Checklist

Use this checklist before finalizing package work that depends on foundation.

## Architecture

- Read the approved plan and reference for the package.
- Confirm every public class, DTO field, config key, event, and command is approved.
- Keep the package descriptor and capability contracts aligned with the approved surface.
- Keep cross-package runtime identifiers technical and stable.

## Bootstrap

- Use `PackageServiceProvider` and Package Tools.
- Keep bindings in `packageRegistered()`.
- Register the descriptor through `PlatformPackageRegistrar` in `packageBooted()`.

## Testing

- Use a foundation-aware Testbench base test case.
- Verify the real provider and registry path.
- Add or update targeted tests for every behavior change.

## Verification

- Run package-appropriate tests.
- Run formatting and static analysis when dependencies are available.
- Report missing tooling explicitly instead of silently skipping it.

## Boundaries

- Do not write host integration code without explicit approval.
- Do not invent new public surface to solve a local implementation shortcut.
- Do not let package-specific business semantics leak into foundation contracts.
