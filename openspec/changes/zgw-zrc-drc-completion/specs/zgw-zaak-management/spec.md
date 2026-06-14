# Capability: zgw-zaak-management — ZRC completion delta

@e2e exclude pure-backend REST controller spec — scenarios covered by PHPUnit/Newman, not Playwright UI tests

## MODIFIED Requirements

### REQ-005: Manage case-bound sub-resources

The system SHALL expose REST CRUD collections for the sub-resources that the ZGW
standard binds to a zaak — audit trail records, zaakbesluiten, zaakeigenschappen,
zaakinformatieobjecten, and zaakobjecten — each scoped to the relevant zaak where
the route provides a `zaakId`. Every sub-resource data endpoint SHALL serve real
data from its backing store; no data endpoint may return 501 Not Implemented.
Verbs that the ZGW standard does not define for a sub-resource (e.g. writes on
the audit trail) SHALL return 405 Method Not Allowed with an `Allow` header.

#### Scenario: Listing zaakeigenschappen for a zaak

- **WHEN** a client lists eigenschappen for a given `zaakId`
- **THEN** the system returns the eigenschappen bound to that zaak as JSON

#### Scenario: No sub-resource stubs remain

- **WHEN** an authenticated client calls any zaak sub-resource data endpoint
  with a verb the ZGW standard defines for it
- **THEN** the response status is never 501 Not Implemented

#### Scenario: Reading the rendered case page

- **WHEN** a client requests a sub-resource page route
- **THEN** the system renders the app template (or an error template on failure)

## ADDED Requirements

### REQ-006: Zaakbesluiten lifecycle

The system SHALL manage ZGW `ZaakBesluit` relation resources on
`/api/zrc/zaken/{zaak_uuid}/besluiten`, backed by OpenRegister `zaakbesluit`
objects in the BRC register. Responses SHALL use the ZGW ZaakBesluit shape
(`url`, `uuid`, `zaak`, `besluit`). Listing SHALL return only the relations
bound to the routed zaak; reading a relation that belongs to a different zaak
SHALL yield 404. Creation SHALL strip any client-supplied `id`, validate that
the routed zaak exists and that the referenced besluit resolves, and SHALL
fail with 400 when the besluit reference is invalid. Deletion SHALL remove the
relation only — never the referenced besluit.

#### Scenario: Listing besluiten of a zaak

- **GIVEN** a zaak with two zaakbesluit relations and another zaak with one
- **WHEN** a client lists `/api/zrc/zaken/{zaak_uuid}/besluiten` for the first zaak
- **THEN** exactly the two relations of that zaak are returned, each shaped as
  `{url, uuid, zaak, besluit}`

#### Scenario: Creating a zaakbesluit validates the besluit reference

- **WHEN** a client posts a zaakbesluit whose `besluit` reference does not resolve
- **THEN** the system responds 400 with a validation error and persists nothing

#### Scenario: Deleting a zaakbesluit keeps the besluit

- **GIVEN** an existing zaakbesluit relation
- **WHEN** the client deletes it
- **THEN** the relation is removed, the underlying besluit still exists, and a
  subsequent list of the zaak's besluiten no longer contains the relation

#### Scenario: Cross-zaak read is refused

- **WHEN** a client requests a zaakbesluit uuid under a `zaak_uuid` it is not
  bound to
- **THEN** the system responds 404

### REQ-007: ZRC zaak audit trail

The system SHALL serve the ZGW audit trail of a zaak on
`/api/zrc/zaken/{zaak_uuid}/audit_trail`, derived from the OpenRegister object
audit trail of that zaak. List and detail responses SHALL map each
OpenRegister audit entry onto the ZGW Audittrail shape (`uuid`, `bron`,
`applicatieWeergave`, `gebruikersId`, `gebruikersWeergave`, `actie`,
`resultaat`, `hoofdObject`, `resource`, `resourceUrl`, `aanmaakdatum`,
`wijzigingen.oud`/`wijzigingen.nieuw`). The audit trail is read-only per the
ZRC standard: create, update and destroy SHALL return 405 Method Not Allowed
with `Allow: GET`. Unauthenticated requests SHALL be rejected with 401.

#### Scenario: Audit trail reflects a zaak mutation

- **GIVEN** a zaak that has been created and then updated
- **WHEN** a client lists `/api/zrc/zaken/{zaak_uuid}/audit_trail`
- **THEN** entries for the create and the update are returned in ZGW
  Audittrail shape, with `wijzigingen` carrying the old and new values of the
  update

#### Scenario: Audit trail writes are refused as 405

- **WHEN** a client POSTs to the zaak audit-trail collection
- **THEN** the system responds 405 Method Not Allowed (not 501) with an
  `Allow: GET` header

#### Scenario: Unauthenticated audit read rejected

- **GIVEN** no logged-in user
- **WHEN** the audit trail is requested
- **THEN** the response is 401 Unauthorized
