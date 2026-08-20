# ui-case-views — REQ-007 e2e Fidelity Delta

**Spec refs**: `ui-case-views`, hydra ADR-008 (testing — "every spec
scenario → browser test verified via Playwright, GIVEN/WHEN/THEN"), ADR-020
(gate diff-scope)

## MODIFIED Requirements

### Requirement: Suspend, resume and extend a zaak from the case detail (REQ-007)

The system SHALL offer suspend, resume and extend actions on the case detail
view for open zaken, each opening its own modal (modal isolation:
`src/modals/`): the suspend modal requires a reden; the extend modal requires
a reden and a duration, pre-capped by the zaaktype's `verlengingstermijn`.
Actions the zaaktype's policy forbids (`opschortingEnAanhoudingMogelijk`,
`verlengingMogelijk`) or that the lifecycle rules refuse (closed, suspended,
already extended) SHALL be hidden or disabled with an explanatory label. A
suspended zaak SHALL show a clearly visible suspension state (with reden and
start date), and after a resume or extension the view SHALL show the
recalculated deadline dates. Each scenario's `@e2e` test MUST assert the
described state transition (opening the action's modal, submitting it, and
observing the resulting detail-view state) — a test that only asserts the
zaken list mounts and an "Add" button is visible does NOT satisfy this
requirement's e2e traceability, even if tagged `@e2e` against one of these
scenarios.

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
  explanatory label — asserted against a seeded zaak/zaaktype fixture with
  both flags disabled, not inferred from the page loading without error
