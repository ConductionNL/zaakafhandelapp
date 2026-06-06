# zaken

## Purpose

Zaken (cases) is the core ZGW domain of zaakafhandelapp. The manifest
exposes a `zaak` index page (route `/zaken`) backed by the
`zaakafhandelapp`/`zaak` register/schema and a `zaak` detail page
(route `/zaken/:id`) with a rich set of sidebar tabs (tasks, roles,
documents, decisions, messages, results, statuses, audit trail). Records
are listed in a table with the columns identificatie, omschrijving,
zaaktype, status and uiterlijkeEinddatumAfdoening.

## Requirements

### Requirement: Cases list view renders

Navigating to `/zaken` SHALL render the cases index page within the app
shell, showing the cases list surface and its create affordance.

#### Scenario: Cases list view renders

- **WHEN** a user navigates to `/zaken`
- **THEN** the app-content area renders
- **AND** the URL is within `/apps/zaakafhandelapp` on the zaken route

#### Scenario: Cases list shows a create affordance

- **WHEN** the cases list is open
- **THEN** a create / add control is available to start a new case

### Requirement: Create-case dialog opens from the list

The cases index SHALL let the user open a create dialog to add a new
case without leaving the list view.

#### Scenario: Create case dialog opens

- **WHEN** the user activates the add/create control on the cases list
- **THEN** a dialog opens with case input fields
- **AND** the user can dismiss the dialog and return to the list

### Requirement: Case detail shell renders with sidebar tabs

The case detail page SHALL render the detail shell so a case handler can
inspect a single case and its related tasks, roles, documents,
decisions, messages, results, statuses and audit trail.

#### Scenario: Case detail route resolves to the detail shell

- **WHEN** a user navigates to a case detail route under `/zaken/`
- **THEN** the app-content area renders the detail shell
- **AND** the app does not crash when the underlying record cannot be loaded
