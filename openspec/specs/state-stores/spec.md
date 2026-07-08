---
retrofit: true
---

# State — Pinia Stores

@e2e exclude Pinia store unit tests — scenarios covered by the existing Jest spec suite, not Playwright UI tests

The Pinia stores that own the client-side state for every ZGW resource (zaken,
besluiten, contactmoment, documenten, resultaten, rol, zaakTypen, berichten,
klanten, medewerkers, taak) plus navigation and search state. Each store holds the
active item and list, exposes setters, and performs the fetch/save/delete API
calls against the app's REST endpoints. Reverse-specified from observed store
behavior.

## Requirements

### REQ-001: Hold and set the active item and list

The system SHALL hold the active resource item, the collection list and any
auxiliary state (e.g. audit trail), and expose setters that wrap raw data into
the resource entity.

#### Scenario: Setting the active item

- **WHEN** a setter is called with raw resource data
- **THEN** the store wraps it in the entity and stores it as the active item

### REQ-002: Refresh a resource collection

The system SHALL fetch a resource collection from its REST endpoint (optionally
with a search query), wrap each record in its entity, and update the list state.

#### Scenario: Refreshing a list

- **WHEN** the refresh action runs
- **THEN** the store fetches the collection and updates the list

### REQ-003: Fetch a single resource and its related data

The system SHALL fetch a single resource by id (and, where applicable, its related
records such as audit trail or linked resources) and update the active item state.

#### Scenario: Fetching one resource

- **WHEN** the get action runs with an id
- **THEN** the store fetches and sets the active item

### REQ-004: Create, update and delete a resource

The system SHALL create, update and delete a resource through its REST endpoint
and refresh the relevant state afterward, returning the response/entities to the
caller.

#### Scenario: Saving a resource

- **WHEN** the save action runs
- **THEN** the store posts/puts the resource and refreshes the list

### REQ-005: Initialise the store registry and navigation/search state

The system SHALL initialise the application's store registry, and manage
navigation (active view/modal/dialog) and search state through dedicated stores.

#### Scenario: Initialising stores

- **WHEN** the app boots
- **THEN** the store registry is initialised and made available
