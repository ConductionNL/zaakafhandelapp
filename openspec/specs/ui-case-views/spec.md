---
retrofit: true
---

# UI — Case Views

The list and detail views for the case-side ZGW resources: zaken, zaaktypen,
zaakeigenschappen, statussen, besluiten, resultaten and documenten. These views
fetch their data from the corresponding stores, present master/detail layouts,
react to selection, and trigger the create/edit/delete modals. Reverse-specified
from observed component behavior.

## Requirements

### REQ-001: Fetch and present a resource collection

The system SHALL fetch the resource collection on mount/refresh and present it as
a selectable list, exposing the current loading/empty state.

#### Scenario: Loading the zaken list

- **WHEN** the zaken view is opened
- **THEN** it fetches the zaken collection and renders the list

### REQ-002: Select and expand a resource for detail

The system SHALL set the active resource item when the user selects a row and
expose its detail, including expanding/collapsing nested rows where the view
supports it.

#### Scenario: Selecting a zaak

- **WHEN** the user clicks a zaak in the list
- **THEN** the view sets it as the active item and shows its detail

### REQ-003: Filter and clear the resource list

The system SHALL filter the presented collection by the user's search text and
clear the filter on request.

#### Scenario: Searching the list

- **WHEN** the user types search text
- **THEN** the view filters the collection accordingly

### REQ-004: Trigger create, edit and delete from a view

The system SHALL open the appropriate modal (create/edit/delete/view) for the
selected resource in response to the user's action.

#### Scenario: Editing a resource

- **WHEN** the user chooses edit on a row
- **THEN** the view sets the active item and opens the edit modal

### REQ-005: Derive presentational helpers

The system SHALL compute presentational values used by the view — icons, status
labels, formatted identifiers and derived ids — from the resource data.

#### Scenario: Rendering a resource icon

- **WHEN** the view renders a resource row
- **THEN** it derives the icon/label from the resource's type or status
