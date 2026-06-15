# zaak-termijn-monitoring Specification

## Purpose
TBD - created by archiving change zaak-termijn-monitoring. Update Purpose after archive.
## Requirements
### Requirement: Derive termijn fields from the zaaktype on creation (REQ-001)

The system SHALL, when a zaak is created without an explicit
`uiterlijkeEinddatumAfdoening` and its zaaktype defines a `doorlooptijd`,
derive `uiterlijkeEinddatumAfdoening` as `startdatum` plus the zaaktype's
`doorlooptijd`; likewise it SHALL derive `einddatumGepland` from
`startdatum` plus the zaaktype's `servicenorm` when absent. Client-supplied
values SHALL never be overridden, derivation SHALL be skipped silently when
the zaaktype does not define the corresponding term or the zaak has no
resolvable zaaktype, and a zaak without a `startdatum` SHALL use its
`registratiedatum` as the derivation base.

#### Scenario: Deriving the uiterste einddatum from the doorlooptijd

- **GIVEN** a zaaktype with `doorlooptijd` of 56 days
- **WHEN** a zaak of that type is created with `startdatum` 2026-06-01 and no
  `uiterlijkeEinddatumAfdoening`
- **THEN** the persisted zaak has `uiterlijkeEinddatumAfdoening` 2026-07-27

#### Scenario: Explicit dates are respected

- **WHEN** a zaak is created with an explicit `uiterlijkeEinddatumAfdoening`
- **THEN** the supplied value is persisted unchanged, regardless of the
  zaaktype's terms

#### Scenario: Zaaktype without terms

- **WHEN** a zaak is created whose zaaktype defines no `doorlooptijd` or
  `servicenorm`
- **THEN** the termijn fields stay as supplied (possibly empty) and creation
  succeeds without error

### Requirement: Declarative deadline notification rules on the zaak schema (REQ-002)

The system SHALL declare approaching-deadline and overdue notification rules
on the zaak schema using the OpenRegister declarative notification dialect
(`x-openregister-notifications`, ADR-031) with the scheduled date-relative
conditions introduced by the OpenRegister change
`notification-engine-scheduled-conditions`: one rule that fires a
configurable number of days (default 7) before `uiterlijkeEinddatumAfdoening`
and one rule that fires when `uiterlijkeEinddatumAfdoening` has passed.
Both rules SHALL be conditioned on the zaak still being open (no `einddatum`
set / archiefstatus nog niet bepaald) and SHALL target the zaak's assigned
behandelaar, falling back to the object owner. The app SHALL contain no
imperative notification dispatch, background job or cron code for deadline
monitoring.

#### Scenario: Approaching-deadline notification

- **GIVEN** an open zaak whose `uiterlijkeEinddatumAfdoening` is 7 days away
- **WHEN** the OpenRegister notification engine evaluates its scheduled
  conditions
- **THEN** the behandelaar receives a Nextcloud notification that the zaak's
  behandeltermijn is approaching, exactly once for that deadline

#### Scenario: Overdue notification

- **GIVEN** an open zaak whose `uiterlijkeEinddatumAfdoening` passed yesterday
- **WHEN** the engine evaluates its scheduled conditions
- **THEN** the behandelaar receives an overdue notification

#### Scenario: Closed zaak triggers nothing

- **GIVEN** a zaak that was closed before its `uiterlijkeEinddatumAfdoening`
- **WHEN** that date passes
- **THEN** no notification is produced for the zaak

#### Scenario: Rules are declarative only

- **WHEN** the app's codebase is inspected
- **THEN** deadline monitoring exists only as `x-openregister-notifications`
  rules in the zaak schema configuration — no BackgroundJob, cron hook or
  imperative notification call (gate-18 clean)

> Status: REQ-002 is NOT YET BUILT — it is blocked on the OpenRegister change
> `notification-engine-scheduled-conditions` (scheduled date-relative
> conditions). REQ-001 (derivation) and REQ-003 (urgency) ship without it; the
> declarative rule block is added once the OR engine supports the condition
> shape. See tasks.md section 2.

### Requirement: Derive a single deadline-urgency state (REQ-003)

The system SHALL derive a deadline-urgency state for a zaak — `op-tijd`,
`bijna-verlopen` (within the configurable lead window, default 7 days, of
`uiterlijkeEinddatumAfdoening` or past `einddatumGepland`), or `verlopen`
(past `uiterlijkeEinddatumAfdoening`) — from the current date and the zaak's
termijn fields. Closed zaken and zaken without termijn fields SHALL have no
urgency state. All UI surfaces (case lists, werkvoorraad, dashboard widgets)
SHALL consume this one derivation so they never disagree.

#### Scenario: Overdue open zaak

- **GIVEN** an open zaak whose `uiterlijkeEinddatumAfdoening` is in the past
- **WHEN** the urgency state is derived
- **THEN** the state is `verlopen`

#### Scenario: Approaching deadline

- **GIVEN** an open zaak whose `uiterlijkeEinddatumAfdoening` is 3 days away
- **WHEN** the urgency state is derived
- **THEN** the state is `bijna-verlopen`

#### Scenario: Closed or undated zaak has no urgency

- **WHEN** the urgency state is derived for a closed zaak, or for a zaak with
  empty termijn fields
- **THEN** no urgency state is returned

