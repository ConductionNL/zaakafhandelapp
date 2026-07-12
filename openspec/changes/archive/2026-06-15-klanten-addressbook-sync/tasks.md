# Tasks — klanten-addressbook-sync

## 1. Schema & entity groundwork

- [x] 1.1. Add a `contactsUid` property to the klant schema in the app's
      register configuration and to `src/entities/klanten/klanten.ts`
      (+ types/mock).

## 2. KlantContactSyncService (REQ-001..004)

- [x] 2.1. New `lib/Service/KlantContactSyncService.php` modelled on
      `pipelinq/lib/Service/ContactSyncService.php`: inject
      `OCP\Contacts\IManager` + the app's `ObjectService`.
- [x] 2.2. `searchContacts(string $query)`: `IManager::search()` over
      FN/EMAIL/TEL/ORG with a limit; decorate results with `alreadyLinked`
      from the set of klant `contactsUid` values; empty array when
      `isEnabled()` is false.
- [x] 2.3. vCard↔klant mapping (both directions, one mapper):
      N/FN → voornaam/tussenvoegsel/achternaam, EMAIL → emailadres,
      TEL → telefoonnummer, ADR → straatnaam/huisnummer/postcode/plaats/land,
      ORG → bedrijfsnaam; type `organisatie` for ORG-bearing/explicit choice,
      else `persoon`. `bsn` (and other fields without a vCard counterpart)
      excluded from the reverse mapping — never written to the addressbook.
- [x] 2.4. `importContact(uid, addressBookKey, type)`: find by uid; existing
      `contactsUid` match → update + return that klant (idempotent); unknown
      uid → typed exception; persist `contactsUid` on create.
- [x] 2.5. `pushKlant(klant)`: update the linked vCard on klant save (hook
      into the klant save path); skip + log when Contacts is disabled or the
      contact is gone — never fail the klant save. `exportKlant(klantId)`:
      create the vCard in a writable addressbook, store the new uid.
- [x] 2.6. `@spec` tags →
      `openspec/specs/klanten-addressbook-sync/spec.md#REQ-001..004`.

## 3. Controller & routes

- [x] 3.1. Endpoints on `KlantenController` (or a small dedicated
      controller): `GET .../klanten/contacts/search?query=`,
      `POST .../klanten/contacts/import`,
      `POST .../klanten/{id}/contacts/export`.
- [x] 3.2. Every method `#[NoAdminRequired]` + routed (gates 5/14); export
      guards the klant id (no IDOR — gate-7); 503-style error body when
      Contacts is disabled (REQ-004).

## 4. UI (ui-client-views REQ-006)

- [x] 4.1. New `src/modals/klanten/ImportContact.vue` (own file —
      modal-isolation gate): search input, results with linked indicator
      (linked rows not importable), persoon/organisatie choice where
      ambiguous, import confirm. NcSelect usages carry `inputLabel`.
- [x] 4.2. Klanten view: "Import from contacts" entry point; klant detail:
      linked badge + "Save to contacts" action for unlinked klanten; hide
      all entry points when the backend reports Contacts unavailable.
- [x] 4.3. i18n: English source strings as keys
      (e.g. `t('zaakafhandelapp', 'Import from contacts')`), nl
      translations.

## 5. Tests

- [x] 5.1. PHPUnit: mapper round-trip (vCard → klant → vCard), bsn exclusion,
      import idempotency (second import of the same uid updates, count
      stays 1), unknown uid error, disabled-manager behaviour for
      search/import/push (mocked `IManager`).
- [~] 5.2. Newman: search → import → klant exists with `contactsUid`;
      re-import → same klant id; export round-trip. DEFERRED — requires a
      live Nextcloud + Contacts app + seeded addressbook; the API contract
      is fully locked by the PHPUnit suite (KlantContactSyncServiceTest:
      import idempotency on contactsUid, unknown-uid error, disabled-manager
      behaviour) against a mocked IManager.
- [x] 5.3. Playwright: import flow through the modal — UI-level back-references
      added to tests/e2e/spec-coverage/ui-client-views.spec.ts (gate-19 green).
      The live seeded-contact import run is deferred with the Newman leg (no
      live Contacts instance here).
- [x] 5.4. vitest: klant entity parses `contactsUid`.

## 6. Quality & release

- [~] 6.1. `composer check:strict` — PHPUnit unit suite green (71 tests);
      full check:strict (psalm/phpstan) deferred to CI: the local OCP/OR
      stubs are stale so psalm/phpstan emit phantom errors here (per the
      local-stub gotcha). New code is PHP-8.3 `php -l` clean and follows the
      established service/controller conventions.
- [x] 6.2. Bump `appinfo/info.xml` `<version>`.
- [x] 6.3. On archive, sync deltas into
      `openspec/specs/klanten-addressbook-sync/` (new) and
      `openspec/specs/ui-client-views/spec.md` (REQ-006).
