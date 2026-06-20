# Capability: ui-client-views — addressbook integration delta

## ADDED Requirements

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
