---
retrofit: true
---

# ZGW Case Lifecycle

@e2e exclude pure-backend PHP service logic spec — scenarios covered by PHPUnit, not Playwright UI tests

## Purpose

Business logic that enforces the ZGW lifecycle and integrity rules as objects are
created, related, transitioned and archived. Covers the cascade relations between
zaken, besluiten and informatieobjecten; status-driven case open/close/reopen
transitions; archive-date calculation; and the validation rules that gate
archiving and decision linking. Reverse-specified from observed service behavior.
## Requirements
### Requirement: Maintain ZGW cascade relations between objects (REQ-001)

The system SHALL, when ZGW objects are created or deleted, maintain the derived
relations the standard requires: linking objectinformatieobjecten to their zaak or
besluit, creating and deleting zaakbesluiten for a besluit, and creating/deleting
zaaktype-informatieobjecttype links — including cleaning up the related objects
when a parent is deleted.

#### Scenario: Creating a besluit links it to its zaak

- **WHEN** a besluit object is created
- **THEN** the system creates the corresponding zaakbesluit relation

#### Scenario: Deleting a besluit cleans up relations

- **WHEN** a besluit is deleted
- **THEN** the system removes the dependent objectinformatieobject / zaakbesluit
  relations

### Requirement: Transition a case open, closed or reopened on status change (REQ-002)

The system SHALL, when a zaak status changes, close the zaak when the status is an
eind-status (setting the close-relevant fields), reopen it when moving away from an
eind-status, and recognise whether a given status array represents an end status.

#### Scenario: Closing a case on end status

- **WHEN** a status that is an eind-status is set on a zaak
- **THEN** the system marks the zaak closed and records the closing data

#### Scenario: Reopening a case

- **WHEN** a zaak that was closed receives a non-eind status
- **THEN** the system reopens the zaak

### Requirement: Apply confidentiality and deletion lifecycle actions (REQ-003)

The system SHALL apply a zaak's vertrouwelijkheidaanduiding (confidentiality
level) across its related objects, and delete a zaak together with its dependent
records.

#### Scenario: Cascading confidentiality

- **WHEN** a zaak's confidentiality level is set
- **THEN** the system propagates it to the related objects

### Requirement: Calculate the archive date (REQ-004)

The system SHALL calculate the archiefactiedatum for a zaak from its
resultaat/archiefnominatie and the configured retention term.

#### Scenario: Computing the archive date

- **WHEN** archive parameters are provided for a zaak
- **THEN** the system returns the calculated archive date

### Requirement: Validate archiving and decision-linking prerequisites (REQ-005)

The system SHALL enforce ZGW validation rules: that a zaak has the required
producten-of-diensten, gegevensgroepen and archive prerequisites before archiving,
that relevante-andere-zaken references are consistent, and that a
besluitinformatieobject is valid before it is linked. Failed checks raise an
error rather than persisting an invalid state.

#### Scenario: Blocking an archive when prerequisites are missing

- **WHEN** a zaak is checked for archiving without the required gegevensgroepen
- **THEN** the system raises a validation error

### Requirement: Suspend and resume a zaak — opschorting (REQ-006)

The system SHALL support suspending an open zaak by setting the ZGW
`opschorting` group (`indicatie = true` plus a mandatory non-empty `reden`),
and resuming it by setting `indicatie = false`. Suspension SHALL be refused
with a validation error when the zaak's zaaktype does not allow it
(`opschortingEnAanhoudingMogelijk` is not true), when the zaak is closed, or
when the zaak is already suspended. While suspended, the system SHALL record
the suspension start date. On resume, the system SHALL shift
`einddatumGepland` and `uiterlijkeEinddatumAfdoening` forward by the elapsed
suspension duration (per Awb art. 4:15 the beslistermijn clock stands still
during suspension), and SHALL retain the last `reden` for the record.

#### Scenario: Suspending an open zaak

- **GIVEN** an open zaak whose zaaktype allows opschorting
- **WHEN** the zaak is updated with `opschorting.indicatie = true` and a reden
- **THEN** the zaak is persisted as suspended with the suspension start
  recorded, and its deadline fields are unchanged

#### Scenario: Resuming shifts the deadlines

- **GIVEN** a zaak with `uiterlijkeEinddatumAfdoening` 2026-07-01 that has
  been suspended for 10 days
- **WHEN** the zaak is resumed (`opschorting.indicatie = false`)
- **THEN** `uiterlijkeEinddatumAfdoening` becomes 2026-07-11 and
  `einddatumGepland` shifts by the same 10 days

#### Scenario: Zaaktype forbids opschorting

- **GIVEN** a zaak whose zaaktype has `opschortingEnAanhoudingMogelijk` false
- **WHEN** a suspension is attempted
- **THEN** the system raises a validation error and persists nothing

#### Scenario: Suspension without a reden is refused

- **WHEN** a suspension is attempted with an empty `reden`
- **THEN** the system raises a validation error

#### Scenario: Closed or already-suspended zaak refuses

- **WHEN** a suspension is attempted on a closed zaak, or on a zaak that is
  already suspended
- **THEN** the system raises a validation error and the zaak is unchanged

### Requirement: Extend a zaak's behandeltermijn — verlenging (REQ-007)

The system SHALL support extending an open, non-suspended zaak once by
setting the ZGW `verlenging` group (mandatory non-empty `reden` and a `duur`
as an ISO 8601 duration). Extension SHALL be refused when the zaaktype does
not allow it (`verlengingMogelijk` is not true), when `duur` exceeds the
zaaktype's `verlengingstermijn` (when set), when the zaak is closed or
suspended, or when the zaak has already been extended (verdaging per Awb
art. 4:14 is single-shot). On a valid extension the system SHALL shift
`einddatumGepland` and `uiterlijkeEinddatumAfdoening` forward by `duur` and
persist the `verlenging` group on the zaak.

#### Scenario: Extending shifts the deadlines

- **GIVEN** an open zaak with `uiterlijkeEinddatumAfdoening` 2026-07-01
  whose zaaktype allows verlenging
- **WHEN** the zaak is extended with `duur` P14D and a reden
- **THEN** `uiterlijkeEinddatumAfdoening` becomes 2026-07-15,
  `einddatumGepland` shifts by 14 days, and the `verlenging` group is
  persisted on the zaak

#### Scenario: Duur exceeds the zaaktype's verlengingstermijn

- **GIVEN** a zaaktype with `verlengingstermijn` P14D
- **WHEN** an extension of P30D is attempted
- **THEN** the system raises a validation error and persists nothing

#### Scenario: Second verlenging is refused

- **GIVEN** a zaak that already carries a persisted `verlenging`
- **WHEN** another extension is attempted
- **THEN** the system raises a validation error

#### Scenario: Zaaktype forbids verlenging

- **GIVEN** a zaak whose zaaktype has `verlengingMogelijk` false
- **WHEN** an extension is attempted
- **THEN** the system raises a validation error

