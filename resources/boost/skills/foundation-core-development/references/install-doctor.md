# Install And Doctor Runtime

Foundation owns two manager-driven operational workflows: install and doctor.

## InstallManager

`InstallManager` orchestrates explicit install steps from enabled packages that implement `DefinesInstallSteps`.

### Rules

- Read packages from `PackageManifestLoader`.
- Validate step ownership before execution.
- Sort steps by priority, then package name, then step key.
- Skip steps whose `shouldRun()` returns `false`.
- Stop at the first thrown exception.
- Return `InstallResult` with normalized status, step references, messages, and optional context.
- Dispatch `WebsiteInstalled` only for successful full runs.

### Status Rules

- `success`: full run with no skips and no package filter
- `partial`: filtered run or any skipped steps with no blocking failure
- `failed`: first blocking failure stops the run

## DoctorManager

`DoctorManager` aggregates `DoctorCheck` instances from enabled packages that implement `ProvidesDoctorChecks`.

### Rules

- Read checks from `PackageManifestLoader`.
- Validate check ownership.
- Run every check and validate the returned `DoctorResult`.
- Sort checks by package name and check key.
- Dispatch `DoctorChecksCompleted` with a small summary array.

### Result Rules

Allowed statuses are:

- `passed`
- `warning`
- `failed`
- `skipped`

Only results with `status === 'failed'` and `isBlocking === true` belong in `failing()`.

## Command Boundary Rule

Keep `website:install` and `website:doctor` thin.
Business logic, ordering, validation, and result shaping belong in the managers.
