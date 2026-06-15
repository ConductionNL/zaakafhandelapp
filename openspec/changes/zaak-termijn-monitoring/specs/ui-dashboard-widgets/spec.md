# Capability: ui-dashboard-widgets — deadline urgency delta

## ADDED Requirements

### REQ-006: Surface deadline urgency in the open-zaken widget

The system SHALL surface deadline urgency in the open-zaken dashboard widget:
each item shows its urgency indicator (per `zaak-termijn-monitoring` REQ-003)
and deadline date, items are ordered most-urgent-first (`verlopen`, then
`bijna-verlopen` by nearest deadline, then the rest), and the widget header
shows the count of overdue zaken when there are any. Activating an item keeps
the existing behaviour (REQ-004) of opening the zaak.

#### Scenario: Overdue zaken float to the top of the widget

- **GIVEN** the open zaken include one overdue zaak and several without
  urgency
- **WHEN** the open-zaken widget renders
- **THEN** the overdue zaak is listed first with an overdue indicator and the
  widget signals 1 overdue zaak

#### Scenario: No overdue zaken, no alarm

- **GIVEN** no open zaak is overdue
- **WHEN** the widget renders
- **THEN** no overdue count is shown and items render as today
