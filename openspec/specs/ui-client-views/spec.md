---
retrofit: true
---

# UI — Client Interaction Views

The list and detail views for the people-and-communication ZGW resources:
klanten, contactmomenten, berichten, medewerkers, rollen and taken. These views
fetch their data from the corresponding stores, present master/detail layouts,
show related records (e.g. a klant's zaken/taken/berichten), and trigger
create/edit/delete modals. Reverse-specified from observed component behavior.

## Requirements

### REQ-001: Fetch and present a client-interaction collection

The system SHALL fetch the resource collection on mount/refresh and present it as
a selectable list with its loading/empty state.

#### Scenario: Loading the klanten list

- **WHEN** the klanten view is opened
- **THEN** it fetches the klanten collection and renders the list

### REQ-002: Select a resource and show related records

The system SHALL set the active item on selection and load/present its related
records (such as a klant's zaken, taken, berichten and contactmomenten, or a
taak's linked zaak).

#### Scenario: Selecting a klant

- **WHEN** the user selects a klant
- **THEN** the view sets it active and loads its related records

### REQ-003: Filter and clear the list

The system SHALL filter the presented collection by search text and clear the
filter on request.

#### Scenario: Searching contactmomenten

- **WHEN** the user types search text
- **THEN** the view filters the contactmomenten list

### REQ-004: Trigger create, edit and delete

The system SHALL open the appropriate modal for the selected resource in response
to the user's action, and react to status changes (e.g. closing or handling a
taak).

#### Scenario: Closing a taak

- **WHEN** the user marks a taak closed
- **THEN** the view updates the taak status and refreshes

### REQ-005: Derive presentational helpers

The system SHALL compute icons, status labels, role labels and formatted values
for display from the resource data.

#### Scenario: Rendering a contact icon

- **WHEN** the view renders a contactmoment
- **THEN** it derives the channel icon (phone/email/agent/mailbox) from the data
