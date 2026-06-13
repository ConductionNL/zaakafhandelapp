# Capability: ui-case-views — deadline urgency delta

## ADDED Requirements

### REQ-006: Surface deadline urgency in case lists and the werkvoorraad

The system SHALL surface each open zaak's deadline-urgency state
(`zaak-termijn-monitoring` REQ-003) in the zaken list views and the personal
werkvoorraad: a visible overdue indicator for `verlopen`, a warning indicator
for `bijna-verlopen`, the deadline date itself on the list item, sorting by
`uiterlijkeEinddatumAfdoening`, and a filter that narrows the list to overdue
zaken. Indicators SHALL use semantic colour variables (NL Design / NC theming,
no hardcoded colours) and SHALL carry a text label so the state is not
conveyed by colour alone (WCAG 1.4.1).

#### Scenario: Overdue zaak is flagged in the werkvoorraad

- **GIVEN** the werkvoorraad contains an open zaak whose
  `uiterlijkeEinddatumAfdoening` is in the past
- **WHEN** the user opens the werkvoorraad
- **THEN** that zaak shows an overdue indicator with a text label and its
  deadline date

#### Scenario: Sorting by deadline

- **WHEN** the user sorts the zaken list by deadline
- **THEN** the zaken are ordered by `uiterlijkeEinddatumAfdoening`, soonest
  first

#### Scenario: Filtering to overdue zaken

- **WHEN** the user applies the overdue filter
- **THEN** only open zaken in the `verlopen` state remain in the list

#### Scenario: Closed zaken show no urgency

- **WHEN** the list renders a closed zaak whose deadline has passed
- **THEN** no urgency indicator is shown for it
