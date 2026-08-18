---
status: proposed
---

# Zaakafhandelapp Leaf Integrations — mail, calendar, contacts, forms

**Spec refs**: `beta-surface-alignment` (no unverified manifest claims),
`openregister/lib/Db/Schema.php` configuration contract (`linkedTypes`,
`mailObjectTemplate`), `openregister/lib/Service/Integration/IntegrationRegistry.php`

Zaakafhandelapp consumes OpenRegister schemas via
`/apps/openregister/api/objects` and owns no register
(`appinfo/info.xml:62-73`; `lib/Settings/` ships no register JSON). Its only
integration leaves today are five `files` widgets in `src/manifest.json`
(lines 412, 559, 785, 939, 986). This capability adds the mail, calendar,
contacts, and forms leaves — schema half contributed to the owning register,
app half declared in `src/manifest.json`.

## ADDED Requirements

### Requirement: Schema-side leaf configuration is contributed, not shipped (REQ-001)

Because zaakafhandelapp owns no register and ships no schema JSON, all
schema-side leaf configuration in this capability (`linkedTypes`,
`mailObjectTemplate`) SHALL be contributed to the schema definitions of the
owning register on the OpenRegister side — the register the admin-wired
`{type}_register` appconfig keys (served by
`lib/Controller/SettingsController.php`) point at. Zaakafhandelapp SHALL NOT
ship `lib/Settings/*_register.json` or any other schema definition, and its
repository diff for this capability SHALL touch only `src/manifest.json`.
Every contributed value MUST satisfy the OpenRegister configuration
validation (`linkedTypes` an array of strings, `mailObjectTemplate` an object
of property-name → scalar; `openregister/lib/Db/Schema.php:2494-2547`).

#### Scenario: Repository diff stays manifest-only

- **GIVEN** the leaf-integrations change is implemented
- **WHEN** the zaakafhandelapp repository diff is inspected
- **THEN** the only non-spec, non-test file modified is `src/manifest.json`,
  and no file under `lib/Settings/` other than the existing PHP admin class
  exists
- @e2e exclude repository-shape assertion — verified by review/grep, not Playwright

#### Scenario: Leaf degrades gracefully without the contributed configuration

- **GIVEN** an instance whose wired register does not (yet) carry the
  contributed `linkedTypes` for a schema
- **WHEN** a detail page containing that schema's leaf widget renders
- **THEN** the leaf MUST render its empty/unconfigured state and the page MUST
  NOT error

### Requirement: Mail leaf — case email linking and contactmoment-from-email (REQ-002)

The `zaak` and `contactmoment` schemas SHALL declare `linkedTypes` including
`email`, and the `contactmoment` schema SHALL declare a
`configuration.mailObjectTemplate` mapping mail-message fields onto the
contactmoment properties the manifest already renders (`notitie` ← message
body, `kanaal` ← the literal `"email"`, `startDate` ← received date, `titel`
← subject), so the Nextcloud Mail sidebar offers creating a contactmoment
from an email. The zaak detail page in `src/manifest.json` SHALL gain an
`{"type": "integration", "integrationId": "email"}` widget so a case shows
its linked email correspondence alongside the existing `zaak-files` leaf.

#### Scenario: Contactmoment created from an email

- **GIVEN** the contributed `mailObjectTemplate` is present on the
  contactmoment schema and a user views an email in the Mail sidebar
- **WHEN** the user invokes the create-from-email action for contactmoment
- **THEN** a contactmoment object MUST be created through
  `/apps/openregister/api/objects` with `notitie`, `kanaal = "email"`,
  `startDate`, and `titel` populated from the message per the template
- @e2e exclude requires a provisioned Mail account on the CI instance — covered by OpenRegister's mail-integration suite; ZAA asserts the template mapping in a schema-configuration contract test

#### Scenario: Zaak detail shows linked emails

- **GIVEN** a zaak with at least one linked email
- **WHEN** a handler opens the zaak detail page
- **THEN** the email leaf widget MUST render the linked correspondence, and a
  zaak without linked email MUST show the leaf's empty state

### Requirement: Calendar leaf — deadlines and appointments on zaak and taak (REQ-003)

The `zaak` and `taak` schemas SHALL declare `linkedTypes` including
`calendar`, and the zaak and taak detail pages in `src/manifest.json` SHALL
each gain a calendar integration leaf widget, so case planning dates the
manifest already surfaces as data fields (`uiterlijkeEinddatumAfdoening`,
`startdatum`/`einddatum` on zaak; `deadline` on taak) and ad-hoc case
appointments can be linked into Nextcloud Calendar from the object's page.

#### Scenario: Case appointment linked from the zaak page

- **GIVEN** a zaak detail page with the calendar leaf rendered
- **WHEN** a handler links or creates a calendar entry from the leaf
- **THEN** the entry MUST be associated with that zaak object and MUST appear
  in the leaf on subsequent visits

#### Scenario: Task deadline visible via the calendar leaf

- **GIVEN** a taak with a `deadline` value and the calendar leaf on the taak
  detail page
- **WHEN** the handler opens the taak detail page
- **THEN** the calendar leaf MUST render, offering the deadline-linked
  calendar surface, without altering the existing `taak-files` leaf or the
  task data widget

### Requirement: Contacts leaf — klant linked to Nextcloud Contacts without duplicating the sync bridge (REQ-004)

The `klant` schema SHALL declare `linkedTypes` including `contacts`, and the
klant detail page in `src/manifest.json` SHALL gain a contacts integration
leaf widget. The leaf SHALL display the same klant ↔ Contacts link that the
existing bespoke bridge maintains
(`lib/Controller/KlantContactsController.php` routes
`contactsStatus`/`searchContacts`/`importContact`/`exportContact`,
`appinfo/routes.php:57-60`, backed by `lib/Service/KlantContactSyncService.php`
and `lib/Service/KlantVCardMapper.php`). There SHALL be exactly one link
record per klant/contact pair: the leaf and the sync bridge MUST read and
write the same linkage, and this change MUST NOT introduce a second,
leaf-private link store.

#### Scenario: Imported contact shows in the klant's contacts leaf

- **GIVEN** a klant created via `klantContacts#importContact` from an existing
  Nextcloud contact
- **WHEN** the klant detail page renders
- **THEN** the contacts leaf MUST show the linked contact — the same link the
  sync bridge recorded, not a parallel one

#### Scenario: No duplicate link surfaces

- **GIVEN** a klant linked to a contact through the leaf
- **WHEN** `klantContacts#searchContacts` flags `alreadyLinked` for that
  contact
- **THEN** the flag MUST be true — both surfaces observe one linkage
- @e2e exclude backend linkage-consistency assertion — verified by PHPUnit against KlantContactSyncService

### Requirement: Forms leaf — aanvraag intake starts or feeds a case (REQ-005)

The `zaak` schema SHALL declare `linkedTypes` including `forms`, and the zaak
detail page in `src/manifest.json` SHALL gain a forms integration leaf
widget, so an intake form (aanvraag) built in Nextcloud Forms can be linked
to the case it started and a handler can open the submission from the case.

#### Scenario: Intake form linked to a zaak

- **GIVEN** a zaak detail page with the forms leaf rendered and an existing
  Nextcloud Forms form
- **WHEN** a handler links the form (or one of its submissions) to the zaak
- **THEN** the leaf MUST show the linked form entry on the zaak, and
  unlinking MUST remove it without touching the zaak's own fields

### Requirement: Manifest archetype notes stay truthful (REQ-006)

Every `src/manifest.json` archetype `_note` whose claim *"declares NO
email/calendar/talk linkedTypes → no comms leaves"* is made untrue by this
capability (zaak line 398, taak line 555, klant line 605, contactmoment line
727) SHALL be rewritten to name the leaves the schema now declares, and
`_note`s for schemas this change does not touch (medewerker 666, bericht 782,
rol 829) SHALL remain unchanged. This applies the `beta-surface-alignment`
cross-surface-truthfulness rule to the manifest itself.

#### Scenario: Notes match declared leaves after the change

- **GIVEN** the implemented manifest
- **WHEN** each archetype `_note` is compared against that schema's
  contributed `linkedTypes` and the page's integration widgets
- **THEN** no `_note` claims the absence of a leaf the page renders, and no
  `_note` claims a leaf the page does not render
- @e2e exclude documentation-consistency assertion — verified by review and a manifest lint check, not Playwright
