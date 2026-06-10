# zaakafhandelapp — deep workflow e2e: BUG LIST

Found while building the DEEP, data-dependent workflow e2e layer
(`tests/e2e/workflows/`). zaakafhandelapp had ZERO backend unit tests, so
none of these were previously caught. All bugs live in the app's OpenRegister
event-listener / ZGW-validation pipeline, which fires on every create / update /
delete of a `zaak` (and on `status` close transitions) the manifest UI performs
directly against OpenRegister.

These are flagged in the specs as `test.fixme` — **no source was modified**.

---

## BUG-1 (CRITICAL) — wrong OpenRegister named parameter `extend:` → 500 on every zaak create/update

**File:** `lib/Service/ZGWZaakLifecycleService.php` line 169

```php
private function find(string $url, array $extend=[]): ObjectEntity
{
    $this->objectService->clearCurrents();
    return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), extend: $extend);
}
```

OpenRegister's `ObjectService::find()` signature is
`find(int|string $id, ?array $_extend = [], bool $files = false, …)` — the extend
parameter is **`$_extend`** (underscore-prefixed control param), not `$extend`.
The call therefore throws `Unknown named parameter $extend` (a fatal `\Error`),
which the event listener surfaces as **HTTP 500** on the `ObjectCreated` /
`ObjectUpdating` zaak path (`ZaakRegisterEventListener` lines 95 / 109 →
`setVertrouwelijkheidaanduiding()` → `find()`).

Observed live: `POST /apps/openregister/api/objects/zaakafhandelapp/zaak`
(with archiefstatus set to get past BUG-3) → **500**; log:
`ZaakAfhandelApp: Error in event handler` +
`Unknown named parameter $extend … ZGWZaakLifecycleService.php line 169`.

Same drift class as the OR `_extend` rule in MEMORY (control params take the
underscore prefix). Fix: call `find(id: …, _extend: $extend)`.

**This is SYSTEMIC — 5 call sites all pass the wrong `extend:` param:**

- `lib/Service/ZGWZaakLifecycleService.php:169`
- `lib/Service/ZGWZaakCloseService.php:178`  (→ 500 on every **status** create)
- `lib/Service/ZGWValidationService.php:99`
- `lib/Service/ZGWLogicService.php:98` and `:159`

Each fires from a different ZGW event-handler branch, so the bug breaks zaak
create/update/delete AND status create. Observed live: `POST
…/objects/zaakafhandelapp/status` → **500**, log:
`Unknown named parameter $extend … ZGWZaakCloseService.php line 178`. Changing a
case's status (the heart of the case workflow) is therefore impossible through
the UI. Fix all five: `extend:` → `_extend:`.

## BUG-2 (CRITICAL) — `setVertrouwelijkheidaanduiding()` crashes on a null `zaaktype`

**File:** `lib/Service/ZGWZaakLifecycleService.php` lines 131-133

```php
$zaakArray = $zaak->jsonSerialize();
$zaaktype  = $this->find($zaakArray['zaaktype']);   // $zaaktype may be null
```

`find()` types `$url` as non-nullable `string`. A zaak created through the UI
form with no `zaaktype` (the form exposes `zaaktype` as a free-text field that a
user can leave blank) makes this
`find(null)` → `TypeError: Argument #1 ($url) must be of type string, null given`
→ **HTTP 500**. Needs a null guard before the `find()` call.

## BUG-3 (HIGH) — case creation is impossible through the manifest UI

The `Zaken` create form (manifest page `Zaken`, schema `zaak`) renders only the
fields `identificatie, omschrijving, status, uiterlijkeEinddatumAfdoening,
zaaktype`. On submit, `ZaakRegisterEventListener::handleObjectCreating()` runs
`ZGWZaakValidationService::checkArchivePrerequisites()`, which **requires**
`archiefnominatie` / `archiefactiedatum` unless `archiefstatus ===
'nog_te_archiveren'`. Because the form exposes none of those fields, every create
is rejected:

Observed live: `POST …/objects/zaakafhandelapp/zaak` → **400**, UI toast
**"Validation failed for zaakafhandelapp-zaak"**, modal stays open, no row
created. A real user therefore cannot create a case from the Cases screen at all.

Even when `archiefstatus: nog_te_archiveren` IS supplied (bypassing the 400),
the create then hits BUG-1 / BUG-2 and 500s. Net effect: **the whole zaak
CRUD + case-workflow path through the UI is non-functional.**

## BUG-4 (MEDIUM) — missing `isset` guards in `checkGegevensgroepen()`

**File:** `lib/Service/ZGWZaakValidationService.php` lines 89, 93

`$arr['verlenging']` / `$arr['opschorting']` are dereferenced without an
`isset()` / null-coalesce guard, emitting
`Undefined array key "verlenging"` / `"opschorting"` warnings on every zaak
create that reaches `checkGegevensgroepen()`.

## BUG-5 (MEDIUM) — zaak delete 500s and leaks the row

Deleting a `zaak` fires `ZaakRegisterEventListener::handleObjectDeleted()` →
`ZGWZaakLifecycleService::deleteZaak()`, which 500s (same `find()`/cascade
issue). `DELETE …/objects/zaakafhandelapp/zaak/{id}` → **500** and the row is
NOT removed. (The fixture cleanup works around this by briefly detaching the
`zaak` schema slug so the listener guard no longer matches — TEST-ONLY.)

---

## Working paths (covered green, not fixme)

- **Klant (customer)** full CRUD-persistence — no ZGW hooks; create/read/edit/
  delete all clean (201/200/204). Covered by `klant-crud-persistence.spec.ts`.
- **Taak (task)** create — clean (201). Covered by `case-workflow.spec.ts`.
- All index list reads for zaak/taak/klant/status (GET 200).

## Environment note (test backing, not a bug)

zaakafhandelapp's manifest UI reads/writes records DIRECTLY through
OpenRegister using register slug `zaakafhandelapp` + schema slugs
`zaak`/`taak`/`klant`/`status`. That register did not exist on the dev instance,
so all data pages 500'd until it was created (OR register id 239 + schemas
744 zaak / 745 taak / 746 klant / 747 status / 754 zaaktype, plus the matching
`*_register` / `*_schema` appconfig). This is fixture/environment setup the app
expects an admin to perform via its Settings screen; it is required for ANY
data to render and is independent of BUG-1..5.
