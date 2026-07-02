---
retrofit: true
---

# UI — Modals & Dialogs

The create/edit/view/delete modals for every ZGW resource (zaken, klanten,
contactmomenten, berichten, medewerkers, taken, besluiten, documenten, rollen,
resultaten, zaaktypen). Each modal collects or displays a resource's data,
validates and submits it through the store, surfaces success/error state, and
closes itself. Reverse-specified from observed component behavior.

## Requirements

### REQ-001: Open and close a resource modal

The system SHALL open a modal seeded with the relevant resource (empty for create,
populated for edit/view) and close it on cancel or after a successful action,
resetting transient state.

#### Scenario: Opening an edit modal

- **WHEN** the user triggers edit
- **THEN** the modal opens populated with the selected resource

### REQ-002: Capture and bind form input

The system SHALL bind the resource's fields to form inputs, including nested and
list fields, and keep the working copy in sync with user edits.

#### Scenario: Editing a field

- **WHEN** the user changes a field in the modal
- **THEN** the working copy reflects the change

### REQ-003: Submit create / update / delete through the store

The system SHALL submit the resource via the corresponding store action,
present the in-flight loading state, and on success update the list and close.

#### Scenario: Saving a resource

- **WHEN** the user confirms a create/edit modal
- **THEN** the modal calls the store save action and closes on success

#### Scenario: Deleting a resource

- **WHEN** the user confirms a delete modal
- **THEN** the modal calls the store delete action and closes on success

### REQ-004: Surface success and error feedback

The system SHALL show success or error feedback from the store action and keep the
modal open with an error state on failure.

#### Scenario: A failed save

- **WHEN** the store save action fails
- **THEN** the modal shows the error and stays open

### REQ-005: Support resource-specific lookups and helpers

The system SHALL support resource-specific behaviors within modals — looking up
related entities, fetching option lists, deriving labels/icons, and formatting
values for display or submission.

#### Scenario: Loading options in a modal

- **WHEN** a modal needs a related option list
- **THEN** it fetches and presents the options
