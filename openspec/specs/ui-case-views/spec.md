---
retrofit: true
---

# UI — Case Views

## Purpose

The list and detail views for the case-side ZGW resources: zaken, zaaktypen,
zaakeigenschappen, statussen, besluiten, resultaten and documenten. These views
fetch their data from the corresponding stores, present master/detail layouts,
react to selection, and trigger the create/edit/delete modals. Reverse-specified
from observed component behavior.
## Requirements
### Requirement: Fetch and present a resource collection (REQ-001)

The system SHALL fetch the resource collection on mount/refresh and present it as
a selectable list, exposing the current loading/empty state.

#### Scenario: Loading the zaken list

- **WHEN** the zaken view is opened
- **THEN** it fetches the zaken collection and renders the list

### Requirement: Select and expand a resource for detail (REQ-002)

The system SHALL set the active resource item when the user selects a row and
expose its detail, including expanding/collapsing nested rows where the view
supports it.

#### Scenario: Selecting a zaak

- **WHEN** the user clicks a zaak in the list
- **THEN** the view sets it as the active item and shows its detail

### Requirement: Filter and clear the resource list (REQ-003)

The system SHALL filter the presented collection by the user's search text and
clear the filter on request.

#### Scenario: Searching the list

- **WHEN** the user types search text
- **THEN** the view filters the collection accordingly

### Requirement: Trigger create, edit and delete from a view (REQ-004)

The system SHALL open the appropriate modal (create/edit/delete/view) for the
selected resource in response to the user's action.

#### Scenario: Editing a resource

- **WHEN** the user chooses edit on a row
- **THEN** the view sets the active item and opens the edit modal

### Requirement: Derive presentational helpers (REQ-005)

The system SHALL compute presentational values used by the view — icons, status
labels, formatted identifiers and derived ids — from the resource data.

#### Scenario: Rendering a resource icon

- **WHEN** the view renders a resource row
- **THEN** it derives the icon/label from the resource's type or status

### Requirement: Surface deadline urgency in case lists and the werkvoorraad (REQ-006)

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

