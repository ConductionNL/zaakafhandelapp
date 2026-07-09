---
retrofit: true
---

# ZGW Related Resources

@e2e exclude pure-backend REST controller spec — scenarios covered by PHPUnit/Newman, not Playwright UI tests

## Purpose

REST management of the ZGW catalogue and decision resources that exist alongside
zaken: besluiten (decisions), documenten/informatieobjecten, resultaten,
rollen, statussen, and zaaktypen. Each is exposed as a uniform REST collection
backed by either the local OpenRegister object store or an upstream ZGW source via
the CallService. Reverse-specified from observed controller behavior.

## Requirements

### Requirement: REQ-001: List and read ZGW resources

The system SHALL list a ZGW resource collection (besluiten, documenten,
resultaten, rollen, statussen, zaaktypen) and read a single resource by id,
returning JSON. Where a controller is backed by an upstream source it delegates
the read through the CallService; otherwise it reads from the object service.
The documenten collection SHALL be served from the DRC document store at
`/api/drc/enkelvoudiginformatieobjecten` with real data; no documenten data
endpoint may return 501 Not Implemented.

#### Scenario: Listing besluiten

- **WHEN** a client lists besluiten
- **THEN** the system returns the besluiten collection as JSON

#### Scenario: Reading a single resultaat

- **WHEN** a client requests a resultaat by id
- **THEN** the system returns that resultaat as JSON

#### Scenario: Listing enkelvoudiginformatieobjecten

- **WHEN** a client lists `/api/drc/enkelvoudiginformatieobjecten`
- **THEN** the system returns the stored informatieobjecten as a JSON
  collection in ZGW shape (never 501)

### Requirement: REQ-002: Create, update and delete ZGW resources

The system SHALL create, update and delete besluiten, documenten, resultaten,
rollen, statussen and zaaktypen, returning the resulting object (or delete result)
as JSON. Creation strips any client-supplied `id`.

#### Scenario: Creating a rol

- **WHEN** a client posts rol data
- **THEN** the system persists it and returns the created rol

#### Scenario: Deleting a status

- **WHEN** a client deletes a status by id
- **THEN** the system removes it and returns the delete result

### Requirement: REQ-003: Render resource pages

The system SHALL render the app template for a ZGW resource page route, returning
an error template if rendering fails.

#### Scenario: Requesting a zaaktypen page

- **WHEN** a client requests the zaaktypen page route
- **THEN** the system returns the rendered app template

### Requirement: REQ-004: Enkelvoudiginformatieobject content in Nextcloud Files

The system SHALL store DRC enkelvoudiginformatieobjecten as ZGW-shaped
metadata objects in the DRC register whose binary content is a real file in
Nextcloud Files under the app's case-documents folder, linked by Nextcloud
file id. Responses SHALL carry the ZGW fields (`url`, `uuid`,
`identificatie`, `bronorganisatie`, `creatiedatum`, `titel`, `auteur`,
`status`, `taal`, `formaat`, `bestandsnaam`, `bestandsomvang`, `versie`,
`informatieobjecttype`, `vertrouwelijkheidaanduiding`) with `inhoud`
resolvable to a download URL; untracked ZGW fields SHALL be null, never
fabricated. Creation SHALL accept base64 `inhoud`, write the decoded bytes to
Nextcloud Files, and derive `bestandsomvang` from the actual stored size.
Updating with new `inhoud` SHALL replace the file content (retaining the old
bytes via Nextcloud file versioning) and increment `versie`. Deletion SHALL
remove both the metadata object and the backing file. A download endpoint
SHALL stream the stored bytes with the document's content type.

#### Scenario: Creating a document stores a real file

- **WHEN** a client posts an enkelvoudiginformatieobject with base64 `inhoud`
  and metadata referencing a zaak
- **THEN** the system writes the decoded content as a file in the case-documents
  folder, persists the metadata with the file link, `versie` 1 and the actual
  `bestandsomvang`, and returns 201 with the ZGW shape

#### Scenario: Downloading returns the original bytes

- **GIVEN** a created enkelvoudiginformatieobject
- **WHEN** the client GETs its download endpoint
- **THEN** the streamed bytes equal the originally uploaded content and the
  response carries the stored content type

#### Scenario: Updating content bumps the version

- **GIVEN** an enkelvoudiginformatieobject at `versie` 1
- **WHEN** the client updates it with new base64 `inhoud`
- **THEN** the backing file content is replaced, `versie` becomes 2, and the
  previous bytes remain recoverable through Nextcloud file versioning

#### Scenario: Deleting removes metadata and file

- **WHEN** the client deletes an enkelvoudiginformatieobject
- **THEN** the metadata object and the backing Nextcloud file are both removed
  and a subsequent read returns 404

