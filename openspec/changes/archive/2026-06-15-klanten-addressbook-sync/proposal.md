# Proposal — klanten-addressbook-sync

## Why

ZAA's klanten are an app-local customer store: the `klant` OR objects
(persoon/organisatie with naam, bsn, telefoonnummer, emailadres, adres, kvk —
`src/entities/klanten/klanten.ts`) live only inside the app. Per company
convention **contacts are a Nextcloud entity** — apps integrate with the NC
addressbook through `OCP\Contacts\IManager` instead of keeping an isolated
person silo ("Contact is a Nextcloud entity"; pipelinq's
`ContactSyncService` is the established pattern:
`pipelinq/lib/Service/ContactSyncService.php` — search with `alreadyLinked`
flag, import via a `contactsUid` link, vCard push-back, graceful degradation
when Contacts is disabled).

Today there is **no addressbook integration anywhere in `lib/`**: a KCC
employee whose municipality already maintains shared addressbooks (ketenpartners,
bewindvoerders, aannemers, interne diensten) must re-type every klant by
hand, and klant data edited in ZAA never reaches the addressbook other apps
(Mail, Talk, Calendar) read. `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md`
lists this as **expected-gap 3**.

Scope note: this is the lighter case-handling frontend, so the integration is
deliberately link-and-sync (search, import, push-back), not a two-way
background reconciliation engine. BRP/Haal Centraal lookup (expected-gap 5)
is a different source with different legal constraints and stays a separate
future change; this change's import surface is designed so a BRP source can
slot in beside the addressbook source later.

## What Changes

- **ADD** capability `klanten-addressbook-sync` (backend, mirroring the
  pipelinq ContactSyncService pattern):
  - **REQ-001 search**: search NC addressbooks (`IContactsManager::search`
    over FN/EMAIL/TEL/ORG) and flag results already linked to a klant via
    the stored `contactsUid`.
  - **REQ-002 import**: import a contact as a klant — vCard → klant field
    mapping, `persoon`/`organisatie` typing, `contactsUid` persisted on the
    klant; idempotent (re-import of a linked uid updates/returns the
    existing klant, never duplicates).
  - **REQ-003 push-back**: saving a linked klant updates its addressbook
    vCard; an unlinked klant can be exported to the addressbook on demand
    (creating the vCard + link).
  - **REQ-004 degradation**: with the Contacts manager unavailable, search
    returns empty, import/export fail with a clear error, and nothing
    breaks.
- **MODIFY** `ui-client-views`: **ADDED REQ-006** — addressbook search +
  import UI in the klanten view (search modal with linked indicator, import
  action, "save to contacts" action and a linked badge on klant detail).
- New `lib/Service/KlantContactSyncService.php` + a thin controller surface
  on the existing `KlantenController` (or a small dedicated controller) with
  routes for search/import/export.

## Impact

### Affected specs

- **ADDED** `specs/klanten-addressbook-sync/spec.md` — REQ-001..004.
- **MODIFIED** `specs/ui-client-views/spec.md` — ADDED REQ-006.

### Affected code

- `lib/Service/KlantContactSyncService.php` — **new**: search, vCard↔klant
  mapping, import, push-back; constructor-injected
  `OCP\Contacts\IManager`.
- `lib/Controller/KlantenController.php` + `appinfo/routes.php` — search /
  import / export endpoints (`#[NoAdminRequired]` + per-object guards; every
  method routed and auth-annotated per gates 5/14).
- `src/entities/klanten/*` — `contactsUid` field.
- The klant schema in the register configuration — `contactsUid` property.
- `src/modals/klanten/ImportContact.vue` (own file, modal isolation),
  klanten view + klant detail (linked badge, save-to-contacts action).
- Tests: PHPUnit (mapping + idempotency with a mocked IManager), Newman
  (endpoints), Playwright (search/import flow), vitest (entity).

### Affected behaviour

- KCC employees pick existing contacts from the municipal addressbooks
  instead of re-typing them; linked klanten stay in sync with the
  addressbook on save, so Mail/Talk/Calendar see the same person.
- Klant data remains an OR object (storage, RBAC, audit via OpenRegister);
  the addressbook holds the contact-card projection — one canonical link
  (`contactsUid`), no second person store.
- Instances without the Contacts app keep working exactly as today; the
  integration surfaces simply hide.

### Citations

- `pipelinq/lib/Service/ContactSyncService.php` — the fleet pattern
  (search + `alreadyLinked` via linked uids, import-as-client/contact,
  vCard sync, `isEnabled()` guard).
- Company convention "Contact is a Nextcloud entity" (reuse
  `OCP\Contacts\IManager`; never invent a customer/contact store) and
  ADR-022 (content types belong in their NC abstraction).
- `src/entities/klanten/klanten.ts` (current isolated klant shape, no
  `contactsUid`); `lib/` (no `OCP\Contacts` usage anywhere today).
- `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` — expected-gap 3.
