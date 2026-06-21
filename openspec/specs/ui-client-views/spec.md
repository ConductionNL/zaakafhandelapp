---
retrofit: true
---

# UI — Client Interaction Views

## Purpose

The list and detail views for the people-and-communication ZGW resources:
klanten, contactmomenten, berichten, medewerkers, rollen and taken. These views
fetch their data from the corresponding stores, present master/detail layouts,
show related records (e.g. a klant's zaken/taken/berichten), and trigger
create/edit/delete modals. Reverse-specified from observed component behavior.

## Requirements

### Requirement: Fetch and present a client-interaction collection (REQ-001)

The system SHALL fetch the resource collection on mount/refresh and present it as
a selectable list with its loading/empty state.

#### Scenario: Loading the klanten list

- **WHEN** the klanten view is opened
- **THEN** it fetches the klanten collection and renders the list

### Requirement: Select a resource and show related records (REQ-002)

The system SHALL set the active item on selection and load/present its related
records (such as a klant's zaken, taken, berichten and contactmomenten, or a
taak's linked zaak).

#### Scenario: Selecting a klant

- **WHEN** the user selects a klant
- **THEN** the view sets it active and loads its related records

### Requirement: Filter and clear the list (REQ-003)

The system SHALL filter the presented collection by search text and clear the
filter on request.

#### Scenario: Searching contactmomenten

- **WHEN** the user types search text
- **THEN** the view filters the contactmomenten list

### Requirement: Trigger create, edit and delete (REQ-004)

The system SHALL open the appropriate modal for the selected resource in response
to the user's action, and react to status changes (e.g. closing or handling a
taak).

#### Scenario: Closing a taak

- **WHEN** the user marks a taak closed
- **THEN** the view updates the taak status and refreshes

### Requirement: Derive presentational helpers (REQ-005)

The system SHALL compute icons, status labels, role labels and formatted values
for display from the resource data.

#### Scenario: Rendering a contact icon

- **WHEN** the view renders a contactmoment
- **THEN** it derives the channel icon (phone/email/agent/mailbox) from the data

### Requirement: Search and import addressbook contacts in the klanten view (REQ-006)

The system SHALL offer an "import from contacts" entry point in the klanten
view that opens a dedicated modal (own file under `src/modals/`): the user
searches the Nextcloud addressbooks, sees results with an already-linked
indicator, and imports a selected contact as a klant (choosing persoon or
organisatie where ambiguous), after which the new klant appears in the list.
A linked klant SHALL show a linked-to-contacts badge on its detail view, and
an unlinked klant SHALL offer a "save to contacts" action. When the Contacts
integration is unavailable, these entry points SHALL be hidden.

#### Scenario: Importing a contact as a klant

- **GIVEN** an addressbook contact "Jan Jansen" and the klanten view
- **WHEN** the user opens the import-from-contacts modal, searches "Jansen",
  selects the contact and confirms the import
- **THEN** a klant "Jan Jansen" appears in the klanten list

#### Scenario: Already-linked contact is indicated

- **GIVEN** a contact that is already linked to a klant
- **WHEN** it appears in the modal's search results
- **THEN** it shows a linked indicator and cannot be imported as a duplicate

#### Scenario: Linked badge and export action

- **WHEN** the user opens the detail of a linked klant
- **THEN** a linked-to-contacts badge is shown
- **AND WHEN** the user opens the detail of an unlinked klant
- **THEN** a "save to contacts" action is available

#### Scenario: Hidden without Contacts

- **GIVEN** the Contacts integration is unavailable
- **WHEN** the user opens the klanten view
- **THEN** the import and export entry points are not shown
