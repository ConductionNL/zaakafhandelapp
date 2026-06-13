# Capability: zgw-case-lifecycle — opschorting & verlenging delta

@e2e exclude pure-backend PHP service logic spec — scenarios covered by PHPUnit/Newman, not Playwright UI tests

## ADDED Requirements

### REQ-006: Suspend and resume a zaak (opschorting)

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

### REQ-007: Extend a zaak's behandeltermijn (verlenging)

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
