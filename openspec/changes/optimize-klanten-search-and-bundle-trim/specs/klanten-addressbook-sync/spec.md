# klanten-addressbook-sync — Bounded alreadyLinked Lookup Delta

**Spec refs**: `klanten-addressbook-sync`, hydra ADR-004 (frontend/build —
bundle discipline)

## MODIFIED Requirements

### Requirement: Search Nextcloud addressbooks for contacts (REQ-001)

The system SHALL search the user's accessible Nextcloud addressbooks via
`OCP\Contacts\IManager` over the FN, EMAIL, TEL and ORG vCard properties and
return matching contacts with their uid, addressbook key, display fields and
an `alreadyLinked` flag that is true when a klant exists whose stored
`contactsUid` equals the contact's uid. Results SHALL be bounded (limit) and
the search SHALL return an empty result set when the Contacts manager is not
enabled. The `alreadyLinked` computation MUST be bounded by the number of
contacts returned from the addressbook search (i.e. scoped to just those
uids) and MUST NOT require reading every klant object in the register to
answer a single search request.

#### Scenario: Searching contacts

- **GIVEN** an addressbook containing a contact "Jan Jansen"
- **WHEN** the user searches for "Jansen"
- **THEN** the contact is returned with uid, addressbook and display fields

#### Scenario: Linked contacts are flagged

- **GIVEN** a contact whose uid is stored as `contactsUid` on an existing
  klant
- **WHEN** that contact appears in search results
- **THEN** its `alreadyLinked` flag is true

#### Scenario: Large klanten registers do not degrade search latency

- **GIVEN** a klanten register containing several thousand `klant` objects
- **WHEN** the user searches contacts and the addressbook manager returns N
  matching contacts (N bounded by the addressbook search's own limit)
- **THEN** the `alreadyLinked` lookup issues a query scoped to at most N
  `contactsUid` values, not a full scan of the klanten register
