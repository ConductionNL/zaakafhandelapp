---
retrofit: true
---

# ZGW Object Data Access

The data-access layer that controllers use to read and persist ZGW objects. It
resolves the right mapper per object type (OpenRegister-backed or local),
translates request parameters into filters/order/pagination, and provides
CRUD + facet + count operations. Also covers the HTTP CallService used to
proxy upstream ZGW sources and the mail notification side effect.
Reverse-specified from observed service behavior.

## Requirements

### REQ-001: Resolve the object mapper and register for an object type

The system SHALL resolve the appropriate mapper for a given object type, and
expose the OpenRegister object service when available (returning null when
OpenRegister is not installed/available).

#### Scenario: Resolving a mapper

- **WHEN** a caller requests the mapper for an object type
- **THEN** the system returns the matching mapper (OpenRegister-backed when present)

### REQ-002: Read objects with filtering, pagination, faceting and counting

The system SHALL read a single object by id (optionally extended), read
collections with filters/sort/search/pagination, read all objects, read multiple
objects by id list, compute facets, and count objects for a given filter set.

#### Scenario: Reading a filtered collection

- **WHEN** a caller queries objects with filters and pagination
- **THEN** the system delegates to the resolved mapper and returns the matching
  objects

#### Scenario: Reading multiple objects by id

- **WHEN** a caller passes a list of ids
- **THEN** the system returns the objects for those ids

### REQ-003: Persist and delete objects

The system SHALL save (create or update, with optional version bump) and delete
objects by id, delegating to the resolved mapper.

#### Scenario: Saving an object

- **WHEN** a caller saves object data for a type
- **THEN** the system persists it via the mapper and returns the stored object

### REQ-004: Build paginated result arrays from request parameters

The system SHALL parse raw request parameters into filters, ordering and
pagination (page/limit/offset, with offset derived from page and limit) and return
a result array suitable for a JSON list response.

#### Scenario: Building a result array

- **WHEN** a controller passes raw request params
- **THEN** the system parses them and returns the matching objects plus pagination
  metadata

### REQ-005: Proxy upstream ZGW sources and send notifications

The system SHALL perform authenticated index/show/create/update/destroy calls
against a configured upstream ZGW source and expose its resolved config, and SHALL
send a mail notification describing the change between an old and new object
version.

#### Scenario: Proxying an upstream index call

- **WHEN** a controller calls the upstream source for a collection
- **THEN** the system issues the authenticated HTTP request and returns the decoded
  response (or null)

#### Scenario: Sending a change notification

- **WHEN** an object transitions from an old to a new state
- **THEN** the system composes and sends a mail describing the change
