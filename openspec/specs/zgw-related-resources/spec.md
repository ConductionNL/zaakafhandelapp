---
retrofit: true
---

# ZGW Related Resources

REST management of the ZGW catalogue and decision resources that exist alongside
zaken: besluiten (decisions), documenten/informatieobjecten, resultaten,
rollen, statussen, and zaaktypen. Each is exposed as a uniform REST collection
backed by either the local OpenRegister object store or an upstream ZGW source via
the CallService. Reverse-specified from observed controller behavior.

## Requirements

### REQ-001: List and read ZGW resources

The system SHALL list a ZGW resource collection (besluiten, documenten,
resultaten, rollen, statussen, zaaktypen) and read a single resource by id,
returning JSON. Where a controller is backed by an upstream source it delegates
the read through the CallService; otherwise it reads from the object service.

#### Scenario: Listing besluiten

- **WHEN** a client lists besluiten
- **THEN** the system returns the besluiten collection as JSON

#### Scenario: Reading a single resultaat

- **WHEN** a client requests a resultaat by id
- **THEN** the system returns that resultaat as JSON

### REQ-002: Create, update and delete ZGW resources

The system SHALL create, update and delete besluiten, documenten, resultaten,
rollen, statussen and zaaktypen, returning the resulting object (or delete result)
as JSON. Creation strips any client-supplied `id`.

#### Scenario: Creating a rol

- **WHEN** a client posts rol data
- **THEN** the system persists it and returns the created rol

#### Scenario: Deleting a status

- **WHEN** a client deletes a status by id
- **THEN** the system removes it and returns the delete result

### REQ-003: Render resource pages

The system SHALL render the app template for a ZGW resource page route, returning
an error template if rendering fails.

#### Scenario: Requesting a zaaktypen page

- **WHEN** a client requests the zaaktypen page route
- **THEN** the system returns the rendered app template
