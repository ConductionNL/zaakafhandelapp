# Capability: ui-case-views — opschorting & verlenging delta

> Numbering note: REQ-006 of `ui-case-views` is allocated by the sibling
> change `zaak-termijn-monitoring` (deadline urgency). If this change
> archives first, renumber this requirement accordingly on sync.

## ADDED Requirements

### REQ-007: Suspend, resume and extend a zaak from the case detail

The system SHALL offer suspend, resume and extend actions on the case detail
view for open zaken, each opening its own modal (modal isolation:
`src/modals/`): the suspend modal requires a reden; the extend modal requires
a reden and a duration, pre-capped by the zaaktype's `verlengingstermijn`.
Actions the zaaktype's policy forbids (`opschortingEnAanhoudingMogelijk`,
`verlengingMogelijk`) or that the lifecycle rules refuse (closed, suspended,
already extended) SHALL be hidden or disabled with an explanatory label. A
suspended zaak SHALL show a clearly visible suspension state (with reden and
start date), and after a resume or extension the view SHALL show the
recalculated deadline dates.

#### Scenario: Suspending from the case detail

- **GIVEN** an open zaak whose zaaktype allows opschorting
- **WHEN** the user activates the suspend action, enters a reden and confirms
- **THEN** the case detail shows the zaak as suspended with the reden and
  the suspension start date

#### Scenario: Resuming shows the shifted deadlines

- **GIVEN** a suspended zaak
- **WHEN** the user resumes it
- **THEN** the case detail shows the zaak as no longer suspended and presents
  the recalculated `uiterlijkeEinddatumAfdoening`

#### Scenario: Extending from the case detail

- **GIVEN** an open zaak whose zaaktype allows verlenging
- **WHEN** the user activates the extend action, enters a reden and a
  duration within the verlengingstermijn, and confirms
- **THEN** the case detail shows the shifted deadline dates and the recorded
  verlenging

#### Scenario: Forbidden actions are not actionable

- **GIVEN** a zaak whose zaaktype forbids opschorting and verlenging
- **WHEN** the user opens the case detail
- **THEN** the suspend and extend actions are hidden or disabled with an
  explanatory label
