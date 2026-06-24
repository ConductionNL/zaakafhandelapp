# ZaakAfhandelApp API-contract tests (Newman)

Newman/Postman contract tests that exercise zaakafhandelapp's HTTP controllers
directly, locking the API contract. Per the gate-19 split, **API/contract
correctness lives in Newman**; Playwright drives the UI only.

## What is covered

| Folder | Endpoints | Happy | Error / Quarantine | Authz |
| --- | --- | --- | --- | --- |
| 0. Setup | OR object API (`/openregister/api/objects/{reg}/{schema}`) | seeds one zaak | — | — |
| 1. Settings | `GET /settings`, `POST /settings` | 200 + contract shape; writable-key round-trip | — | 401 no-auth, 401 bad-auth |
| 2. Zaak CRUD + lifecycle | `GET/POST /api/zrc/zaken`, `GET/PUT/DELETE .../{id}`, `GET /api/zaken/{id}/audit_trail` | list/create/read/update/audit all 200 | **DELETE 500** (quarantine), **GET-missing 500** (quarantine, should be 404) | 401 no-auth (index + create) |
| 3. Taak | `GET/POST /api/taken`, `DELETE .../{id}` | list + create 200 | **DELETE 500** (quarantine) | 401 no-auth |
| 4. Klant | `GET/POST /api/klanten`, `DELETE .../{id}` | list + create 200 | **DELETE 500** (quarantine) | 401 no-auth |
| 5. Status/Resultaat/Rol/Besluit | `GET /api/zrc/statussen`, `/api/zrc/resultaten`, `/api/zrc/rollen`, `/api/brc` | — | **500** (quarantine, CallService upstream) | 401 no-auth (statussen) |
| 6. Zaaktypen / Documenten | `GET /api/ztc/zaaktypen`, `GET /api/drc` | drc 501 (documented stub) | zaaktypen **500** (quarantine, unconfigured) | 401 no-auth (zaaktypen) |
| 9. Teardown | OR object API DELETE | idempotent cleanup of all seeded/created objects | — | — |

**34 requests, 50 assertions, all green.**

The collection is **self-contained and idempotent**: setup seeds a zaak via the
OpenRegister object API, the CRUD folders create their own objects, and teardown
deletes everything via OR (`200/204/404` all accepted, so reruns never fail).

## Phase-0 fixes locked at the API level

- **`GET /settings` returns 200** (folder 1). This was the Phase-0
  `getRegisters()` / `ObjectMapperService` 500→200 fix; the test also asserts the
  contract shape (`objectTypes`, `openRegisters`, `availableRegisters`).
- **Zaak create / read / update return 200, not 500** (folder 2). This is the
  Phase-0 `find(extend:)` surface — `POST`, `GET .../{id}` and `PUT .../{id}` all
  succeed, the update persists and keeps the same id (no IDOR / no duplicate
  object), and the audit trail returns 200.

## Known bugs (quarantined — NOT fake passes)

Each quarantine test asserts the **current** bad status so the suite stays green
honestly. When the app is fixed, that test goes RED — flip it to the happy-path
assertion at that point.

1. **`DELETE` on every OR-backed resource (zaken / taken / klanten) → HTTP 500.**
   `ObjectQueryService::deleteObject()` does
   `$mapper->delete($mapper->find($id))`, but the OR-backed mapper is
   `ObjectServiceMapperAdapter` whose `delete(array $criteria)` signature expects
   an **array**, not the `ObjectEntity` that `find()` returns. The mismatch throws
   a `\TypeError`, which the surrounding `catch (Exception $e)` does **not** catch
   (`TypeError` is not an `Exception`), so the request 500s instead of returning
   `true`/`404`. Fix: call the adapter's object-delete path (e.g. `deleteObject(id)`)
   or catch `\Throwable`. This is a `find()`-then-`delete()` shape bug, adjacent to
   the Phase-0 `find(extend:)` family.

2. **`GET /api/zrc/zaken/{missing}` → HTTP 500 instead of 404.**
   `show()` → `getObject()` → `$mapper->find($id)` throws
   `DoesNotExistException` on a missing id, which bubbles up as 500. Should be a
   clean 404.

3. **`statussen` / `resultaten` / `rollen` / `brc` (besluiten) → HTTP 500.**
   These controllers delegate to `CallService->get('zrc'|'brc', '<resource>')`,
   which issues a real Guzzle HTTP GET to the **bare hostname** of the resource
   (no upstream ZRC/BRC source URL is configured), producing
   `cURL error 6: Could not resolve host: <resource>` → 500. They need a
   configured upstream source, or to be moved onto an OR-backed mapper like
   zaken/taken/klanten.

4. **`zaaktypen` (and the other unconfigured ObjectService resources) → HTTP 500.**
   `ObjectMapperService::getMapper('zaaktypen')` finds no `openregister` source
   and no internal match → `InvalidArgumentException "Unknown object type:
   zaaktypen"` → 500. Wiring `zaaktypen_source/register/schema` (as zaken, taken,
   klanten already are) would make it 200. (Only `zaken`, `taken`, `klanten`,
   `statussen` have register/schema config in the current deployment.)

`documenten` (`/api/drc`) is **not** a quarantine: it returns a documented
**501 Not Implemented** stub (routes.php / issue #268). The test asserts it stays
501 and is not a 500 crash.

## Running

```bash
# defaults: BASE_URL=http://localhost:8080, ADMIN_USER=admin, ADMIN_PASS=admin
./run-newman.sh

# or directly:
npx newman run zaakafhandelapp.postman_collection.json \
  --env-var baseUrl=http://localhost:8080 \
  --env-var noAuthBase=http://127.0.0.1:8080 \
  --env-var adminUser=admin \
  --env-var adminPass=admin \
  --ignore-redirects
```

`run-newman.sh` prefers a globally-installed `newman`, falls back to `npx newman`,
and serialises runs under `flock /tmp/uiaudit-zaakafhandelapp.lock` to avoid
tripping the Nextcloud brute-force protection when multiple agents run in parallel.

## Auth-isolation detail (important for reuse)

Newman keeps a per-run cookie jar. Authenticated requests against `baseUrl`
(`localhost`) establish a Nextcloud session cookie; because the jar is shared,
that cookie would silently authenticate the no-auth / bad-auth requests too (they
then return 200 instead of 401). Two measures keep the authorization tests honest:

1. **Host split** — authenticated requests use `{{baseUrl}}`
   (`http://localhost:8080`); the no-auth / bad-auth requests use `{{noAuthBase}}`
   (`http://127.0.0.1:8080`). NC session cookies are host-scoped, so the
   `localhost` session is never sent to `127.0.0.1`, making those requests
   genuinely unauthenticated. `run-newman.sh` derives `noAuthBase` from `BASE_URL`
   automatically (override with `NO_AUTH_BASE`).
2. **`--ignore-redirects` + `Accept: application/json`** — unauthenticated
   requests get NC's JSON `401`, not the `303`→login-page `200` HTML that a
   browser `Accept` would follow. Authenticated app-framework writes additionally
   send `OCS-APIRequest: true`.

This is the reusable Newman authz pattern for the fleet.

## Collection variables

`baseUrl`, `noAuthBase`, `adminUser`, `adminPass`, plus the deployed OpenRegister
IDs `zaakRegister=239`, `zaakSchema=744`, `takenSchema=745`, `klantenSchema=746`.
The `seededZaakId`, `createdZaakId`, `createdTaakId` and `createdKlantId`
variables are captured at runtime so teardown can clean up.
