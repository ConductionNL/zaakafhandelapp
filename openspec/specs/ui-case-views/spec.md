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

