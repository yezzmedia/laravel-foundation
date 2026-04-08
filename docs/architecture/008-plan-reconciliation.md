# 008 Plan Reconciliation

This document captures the current implementation reality for the 001/008 security-governance rollout.

It exists because the canonical plan files under `/home/yezz/Developement/plan/website/packages/yezzmedia/foundation/docs/architecture/` are read-only in this workspace session and therefore cannot be updated in place here.

## Purpose

Use this file as the transfer source for updating the canonical planning documents so the addenda can be folded back into the main plan set.

## Current implementation summary

The delivered architecture is now broader than the original read-only 008 posture plan.

The actual implemented split is:

- `yezzmedia/laravel-foundation`
  - provides `DefinesSecurityRequests`
  - provides `DefinesSecurityRequirements`
  - provides `SecurityRequestDefinition`
  - provides `SecurityRequirementDefinition`
  - provides `SecurityRequestRegistry`
  - provides `SecurityRequirementRegistry`
  - validates and registers both declaration types in `PlatformPackageRegistrar`
- `yezzmedia/laravel-ops`
  - declares login-throttle intent for the ops panel
- `yezzmedia/laravel-access`
  - declares privileged-account MFA intent
  - emits runtime visibility through the optional ops-security broker
- `yezzmedia/laravel-ops-settings`
  - declares password-confirmation requirements for destructive settings changes
  - enforces that workflow in the package-owned page layer
- `yezzmedia/laravel-ops-security`
  - retains posture checks for SSL, SSH, secrets, and config
  - aggregates package-declared requests and requirements
  - computes effective controls
  - verifies selected controls without taking over all enforcement
  - persists request, decision, and runtime-evidence visibility when the store is ready
  - renders operator-facing governance and visibility in the ops panel

## Canonical plan updates required

### `001-foundation-laravel.md`

Fold in the former addendum content and mark the security-governance declaration surface as implemented.

Required additions:

- new capability contracts:
  - `DefinesSecurityRequests`
  - `DefinesSecurityRequirements`
- new DTOs:
  - `SecurityRequestDefinition`
  - `SecurityRequirementDefinition`
- new registries:
  - `SecurityRequestRegistry`
  - `SecurityRequirementRegistry`
- registrar validation vocabulary:
  - domains: `auth`, `identity`, `session`, `transport`, `runtime`, `secrets`
  - levels: `required`, `recommended`, `optional`, `disallowed`
  - enforcement modes: `observe_only`, `package_owned`, `centrally_enforced`
- rule that foundation remains declaration-only and does not enforce runtime auth behavior

### `001-foundation-reference.md`

Add the concrete building blocks that are now shipped:

- `Contracts\DefinesSecurityRequests`
- `Contracts\DefinesSecurityRequirements`
- `Data\SecurityRequestDefinition`
- `Data\SecurityRequirementDefinition`
- `Registry\SecurityRequestRegistry`
- `Registry\SecurityRequirementRegistry`

Update `PlatformPackageRegistrar` and `FoundationServiceProvider` responsibilities to include registration, validation, binding, and sealing of these registries.

### `008-ops-security-laravel.md`

Change the plan status from `Planned — V1` to implemented.

Required mission and boundary updates:

- keep read-oriented posture monitoring for SSL, SSH, secrets, and config
- add the central governance role:
  - aggregate security requests and requirements declared by producer packages
  - compute effective policy
  - verify selected controls without taking over all enforcement
  - record visibility evidence
- remove the outdated statement that posture data is never persisted
  - posture summaries remain read-oriented
  - visibility records are now persisted in dedicated tables when available

### `008-ops-security-reference.md`

The reference needs the largest update.

It should mark the original posture blocks as implemented and add the new shipped blocks:

- governance DTOs:
  - `EffectiveSecurityControl`
  - `SecurityGovernanceSummary`
  - `SecurityVisibilitySummary`
  - `SecurityRequestRecordData`
  - `SecurityDecisionRecordData`
  - `SecurityRuntimeEvidenceData`
- visibility contracts, support, and models:
  - `Contracts\SecurityRequestBroker`
  - `Support\DatabaseSecurityRequestBroker`
  - `Support\OpsSecurityVisibilityStoreSetup`
  - `Support\SecurityDecisionResolver`
  - `Support\SecurityPayloadSanitizer`
  - `Models\SecurityRequestRecord`
  - `Models\SecurityDecisionRecord`
  - `Models\SecurityRuntimeEvidence`
  - visibility migration `0001_create_ops_security_visibility_tables.php`
- governance doctor checks:
  - `Doctor\LoginThrottleCheck`
  - `Doctor\PasswordConfirmationCheck`
  - `Doctor\PrivilegedMfaCheck`
  - `Doctor\SecurityDriftCheck`
  - `Doctor\SecurityPolicyConflictCheck`
- install step:
  - `Install\EnsureOpsSecurityVisibilityStoreReadyInstallStep`

The contribution tables must also be updated to the shipped counts:

- permissions: 2
- feature flags: 1
- audit events: 1
- install steps: 4
- ops modules: 1
- doctor checks: 10
- security requests: 2
- security requirements: 2

### `008-ops-security-governance-addendum.md`

This addendum is now implemented in substance and should be folded into the canonical 008 docs, then removed from the active addendum set.

Transferred outcomes now live in runtime code:

- declaration layer in foundation
- producer rollout in ops, access, and ops-settings
- central aggregation, verification, and visibility in ops-security

### `001-foundation-security-governance-addendum.md`

This addendum is also implemented in substance and should be folded into the canonical 001 docs, then removed from the active addendum set.

## Recommended closure note for the canonical plan set

Suggested status wording:

> 008 is delivered as a combined security posture and governance package. The original read-oriented posture scope remains, and the governance addendum has been folded into the canonical plan after implementation across foundation, ops, access, ops-settings, and ops-security.

## Deletion guidance

After the canonical 001 and 008 files have been updated in the plan repository:

- delete `008-ops-security-governance-addendum.md`
- delete `001-foundation-security-governance-addendum.md`

Those deletions should happen only in the writable plan repository after the above content has been copied into the main plan files.
