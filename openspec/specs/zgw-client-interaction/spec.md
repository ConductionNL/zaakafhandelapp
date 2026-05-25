---
retrofit: true
---

# ZGW Client Interaction

Management of the people-and-communication resources around case handling:
klanten (customers/citizens), medewerkers (employees), contactmomenten (contact
moments), berichten (messages) and taken (tasks). Each is a uniform REST
collection; klanten additionally exposes its related zaken, taken, berichten and
contactmomenten. Reverse-specified from observed controller behavior.

## Requirements

### REQ-001: List and read client-interaction resources

The system SHALL list and read by id the klanten, medewerkers, contactmomenten,
berichten and taken collections, returning JSON, with list results filtered and
ordered by request parameters.

#### Scenario: Listing contactmomenten

- **WHEN** a client lists contactmomenten with filters
- **THEN** the system returns the matching contactmomenten as JSON

### REQ-002: Create, update and delete client-interaction resources

The system SHALL create, update and delete klanten, medewerkers, contactmomenten,
berichten and taken, returning the resulting object or delete result as JSON.
Creation strips any client-supplied `id`; a delete returns HTTP 404 when the
object did not exist.

#### Scenario: Creating a taak

- **WHEN** a client posts taak data
- **THEN** the system persists it and returns the created taak

### REQ-003: Read related resources of a klant

The system SHALL return, for a given klant, the zaken, taken, berichten and
contactmomenten related to that klant.

#### Scenario: Fetching a klant's zaken

- **WHEN** a client requests the zaken of a klant id
- **THEN** the system returns the related zaken as JSON

### REQ-004: Read audit trails and render pages

The system SHALL return the audit trail of a client-interaction resource by id,
and render the app template for the resource page route (or an error template on
failure).

#### Scenario: Requesting a bericht audit trail

- **WHEN** a client requests the audit trail for a bericht id
- **THEN** the system returns the audit trail entries as JSON
