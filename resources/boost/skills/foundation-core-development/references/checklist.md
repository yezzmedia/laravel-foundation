# Foundation Core Checklist

Use this checklist before finalizing internal foundation changes.

## Surface

- Confirm the change stays within the approved foundation V1 surface.
- Check whether DTO fields, events, commands, manager returns, or config keys would change.
- Align plan and reference before changing any approved public expectation.

## Runtime

- Keep descriptor registration explicit through `PlatformPackageRegistrar`.
- Preserve disabled-package behavior.
- Preserve sealing behavior unless an explicit runtime rule is being changed.
- Keep event payloads technical and compact.

## Testing

- Add or update targeted tests for validation, ordering, result shapes, or normalization rules.
- Use the real provider path when container wiring or boot order matters.
- Keep tests close to runtime behavior.

## Verification

- Run formatting and static analysis when tooling is available.
- Run the smallest relevant test scope first, then broader checks as needed.
- Report missing or unavailable tooling explicitly.
