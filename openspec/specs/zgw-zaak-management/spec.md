---
retrofit: true
---

# ZGW Zaak Management

@e2e exclude pure-backend REST controller spec — scenarios covered by PHPUnit/Newman, not Playwright UI tests

Case (zaak) management surface implementing the VNG GEMMA Zaken standard
(https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/). Exposes a REST
collection over the `zaken` register, plus the case-bound sub-resources that the
ZGW standard attaches to a zaak (audit trail, besluiten, eigenschappen,
informatieobjecten, objecten). Reverse-specified from observed controller
behavior.

## Requirements

### REQ-001: List and search zaken

The system SHALL return the full collection of zaken, filtered and ordered by the
request parameters, as a JSON response.

#### Scenario: Listing zaken with filters

- **WHEN** a client issues `GET` against the zaken collection with query parameters
- **THEN** the system resolves the parameters into filters/order and returns the
  matching zaken as a JSON result array

### REQ-002: Read a single zaak

The system SHALL return a single zaak identified by its id as a JSON response.

#### Scenario: Fetching one zaak

- **WHEN** a client requests a zaak by id
- **THEN** the system returns that zaak object as JSON

### REQ-003: Create, update and delete a zaak

The system SHALL create a new zaak from the posted parameters (ignoring any
supplied `id`), update an existing zaak by id, and delete a zaak by id, each
returning a JSON response. A delete returns `success` true with HTTP 200 when the
object existed, otherwise HTTP 404.

#### Scenario: Creating a zaak

- **WHEN** a client posts zaak data
- **THEN** the system strips any `id`, persists the object, and returns the created
  zaak

#### Scenario: Deleting a missing zaak

- **WHEN** a client deletes a zaak id that does not exist
- **THEN** the system returns `{success:false}` with HTTP 404

### REQ-004: Read the audit trail of a zaak

The system SHALL return the audit trail entries for a zaak identified by its id.

#### Scenario: Requesting a zaak audit trail

- **WHEN** a client requests the audit trail for a zaak id
- **THEN** the system returns the audit trail entries as JSON

### REQ-005: Manage case-bound sub-resources

The system SHALL expose REST CRUD collections for the sub-resources that the ZGW
standard binds to a zaak — audit trail records, zaakbesluiten, zaakeigenschappen,
zaakinformatieobjecten, and zaakobjecten — each scoped to the relevant zaak where
the route provides a `zaakId`.

#### Scenario: Listing zaakeigenschappen for a zaak

- **WHEN** a client lists eigenschappen for a given `zaakId`
- **THEN** the system returns the eigenschappen bound to that zaak as JSON

#### Scenario: Reading the rendered case page

- **WHEN** a client requests a sub-resource page route
- **THEN** the system renders the app template (or an error template on failure)
