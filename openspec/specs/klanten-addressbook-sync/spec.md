# klanten-addressbook-sync Specification

## Purpose
TBD - created by archiving change klanten-addressbook-sync. Update Purpose after archive.
## Requirements
### Requirement: Search Nextcloud addressbooks for contacts (REQ-001)

The system SHALL search the user's accessible Nextcloud addressbooks via
`OCP\Contacts\IManager` over the FN, EMAIL, TEL and ORG vCard properties and
return matching contacts with their uid, addressbook key, display fields and
an `alreadyLinked` flag that is true when a klant exists whose stored
`contactsUid` equals the contact's uid. Results SHALL be bounded (limit) and
the search SHALL return an empty result set when the Contacts manager is not
enabled.

#### Scenario: Searching contacts

- **GIVEN** an addressbook containing a contact "Jan Jansen"
- **WHEN** the user searches for "Jansen"
- **THEN** the contact is returned with uid, addressbook and display fields

#### Scenario: Linked contacts are flagged

- **GIVEN** a contact whose uid is stored as `contactsUid` on an existing
  klant
- **WHEN** that contact appears in search results
- **THEN** its `alreadyLinked` flag is true

### Requirement: Import a contact as a klant (REQ-002)

The system SHALL import a Nextcloud contact into a klant OR object: vCard
properties map onto the klant fields (N/FN → naam parts, EMAIL → emailadres,
TEL → telefoonnummer, ADR → adres parts, ORG → bedrijfsnaam), the klant
`type` is `organisatie` when importing as an organisation (ORG-bearing card,
or by explicit caller choice) and `persoon` otherwise, and the contact's uid
is persisted as the klant's `contactsUid`. Import SHALL be idempotent: when
a klant with that `contactsUid` already exists, the import updates and
returns that klant instead of creating a duplicate. Importing a uid that
cannot be found in any addressbook SHALL fail with a clear error.

#### Scenario: Importing a person contact

- **GIVEN** a contact with name, email and phone properties
- **WHEN** the user imports it as a klant
- **THEN** a klant of type `persoon` is created with the mapped fields and
  the contact's uid as `contactsUid`

#### Scenario: Re-import does not duplicate

- **GIVEN** a klant linked to contact uid X
- **WHEN** contact X is imported again
- **THEN** no new klant is created; the existing klant is updated from the
  current vCard values and returned

#### Scenario: Unknown uid fails cleanly

- **WHEN** an import is requested for a uid not present in any accessible
  addressbook
- **THEN** the system responds with a clear error and persists nothing

### Requirement: Push a klant to the addressbook (REQ-003)

The system SHALL, when a klant that carries a `contactsUid` is saved, update
the linked addressbook contact's vCard from the klant's current field values
(reverse of the REQ-002 mapping). For a klant without a `contactsUid` the
system SHALL offer an explicit export operation that creates the vCard in a
writable addressbook and stores the new uid as the klant's `contactsUid`.
Privacy-sensitive klant fields that have no vCard counterpart (e.g. `bsn`)
SHALL never be written to the addressbook.

#### Scenario: Saving a linked klant updates the vCard

- **GIVEN** a klant linked to an addressbook contact
- **WHEN** the klant's telefoonnummer is changed and saved
- **THEN** the linked contact's TEL property reflects the new value

#### Scenario: Exporting an unlinked klant

- **GIVEN** a klant without a `contactsUid`
- **WHEN** the user exports it to the addressbook
- **THEN** a contact is created with the mapped klant fields and the klant
  now stores its uid as `contactsUid`

#### Scenario: BSN never reaches the addressbook

- **WHEN** a klant with a bsn is exported or synced
- **THEN** the written vCard contains no bsn value

### Requirement: Degrade gracefully without the Contacts app (REQ-004)

The system SHALL behave safely when `OCP\Contacts\IManager::isEnabled()` is
false: contact search returns an empty list, import and export operations
fail with a clear service-unavailable error, and saving a linked klant
persists the klant normally while skipping the vCard push (logged, not
fatal).

#### Scenario: Search with Contacts disabled

- **GIVEN** the Contacts manager reports not enabled
- **WHEN** the user searches contacts
- **THEN** an empty result set is returned without error

#### Scenario: Klant save survives Contacts being disabled

- **GIVEN** a linked klant and a disabled Contacts manager
- **WHEN** the klant is saved
- **THEN** the klant update persists and the skipped vCard push is logged,
  not raised

