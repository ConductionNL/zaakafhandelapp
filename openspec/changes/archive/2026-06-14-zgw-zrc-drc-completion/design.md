# Design — zgw-zrc-drc-completion

## Context

Three ZGW API surfaces are deliberate 501 stubs ("so that clients never
receive fabricated test data", issue #268): the ZRC `zaakbesluiten` and
`audit_trail` sub-resources and the entire DRC. This design completes them on
top of the existing OR plumbing without inventing new abstractions.

## D1 — Zaakbesluiten: relation objects, not duplicated besluiten

Per ZRC 1.5.1, `/zaken/{zaak_uuid}/besluiten` is a *mirror* of the BRC
besluit↔zaak relation: the `ZaakBesluit` resource is just
`{url, uuid, zaak, besluit}`. We therefore:

- store `zaakbesluit` objects in OpenRegister using the slugs
  `ZGWRegistryService::getBrcRegister()` (`besluiten`) +
  `getZaakBesluitSchema()` (`zaakbesluit`) — both already exist in the
  registry service;
- `index` filters zaakbesluit objects on `zaak == {zaak_uuid}`; `show`
  additionally asserts the fetched object belongs to the routed zaak (404
  otherwise — no cross-zaak reads, IDOR-safe per ADR-005);
- `create` strips any client `id`, validates the routed zaak exists
  (`ObjectService` read on `zaken`) and the referenced `besluit` resolves
  (local OR read; the `besluiten` collection is CallService/BRC-backed, so a
  besluit given as URL/uuid of the local app is resolved locally and an
  external URL is accepted verbatim — ZGW allows remote BRC references);
- `update` is allowed (ZGW models it as delete+create; we accept PUT on the
  relation for the registered resource quintet but only `besluit` may
  change — `zaak` is fixed by the route);
- `destroy` deletes the relation object only, never the besluit.

Rejected alternative: deriving zaakbesluiten from OR object *relations* on
the zaak object — relations carry no stable ZGW uuid/url per link, and the
ZGW contract requires addressable ZaakBesluit resources.

## D2 — Zaak audit trail: map OR AuditTrail → ZGW Audittrail, read-only

`ObjectService::getAuditTrail('zaken', $id)` already returns serialized OR
`AuditTrail` entities (via OR `getLogs()`); `zaken#getAuditTrail` already
serves them raw on the in-app route. The ZRC route gets a thin mapper
(private to the controller or a small `ZGWAuditTrailMapper`):

| ZGW Audittrail field | Source |
|---|---|
| `uuid` | OR audit entry uuid/id |
| `bron` | constant `"ZRC"` |
| `applicatieWeergave` | constant `"Zaak Afhandel App"` |
| `gebruikersId` / `gebruikersWeergave` | OR entry user / user name |
| `actie` / `actieWeergave` | OR action (`create`/`update`/`delete`) mapped to ZGW verbs |
| `resultaat` | HTTP-ish result code of the logged mutation (200/201/204) |
| `hoofdObject` | absolute URL of the zaak (`/api/zrc/zaken/{uuid}`) |
| `resource` / `resourceUrl` / `resourceWeergave` | `"zaak"` + zaak URL |
| `aanmaakdatum` | OR entry created timestamp (ISO 8601) |
| `wijzigingen.oud` / `wijzigingen.nieuw` | OR entry changed-values payload |

ZRC defines the audit trail as **read-only** (GET list + GET detail). The
resource quintet stays registered in `routes.php` for gate-14
(route-reachability), so `create`/`update`/`destroy` return
**405 Method Not Allowed** with `Allow: GET` — semantically correct, unlike
the current 501 ("not implemented yet"), and it keeps the no-fabricated-data
guarantee. Unauthenticated requests keep returning 401.

## D3 — DRC: metadata in OR, bytes in Nextcloud Files

**Split:** `enkelvoudiginformatieobject` metadata lives as OR objects (DRC
register `documenten`, new schema slug `enkelvoudiginformatieobject` added
to `ZGWRegistryService::SCHEMAS`); binary content lives as real files so NC
core gives preview, sharing and versioning for free (expected-gap 4).

**Folder layout:** an app-managed folder in the acting user's files —
`Zaakdocumenten/{zaak identificatie or uuid}/{bestandsnaam}` — created
lazily by the new `CaseDocumentService` (via `IRootFolder::getUserFolder()`).
The OR metadata object stores the NC `fileId` so renames/moves don't break
the link. (Group folders are a deployment concern, out of scope; the service
isolates the storage decision behind one class so it can be swapped.)

**Payload shape (DRC 1.5.0 subset, honest):** `url`, `uuid`,
`identificatie`, `bronorganisatie`, `creatiedatum`, `titel`, `auteur`,
`status`, `taal`, `formaat`, `bestandsnaam`, `bestandsomvang`, `versie`,
`beginRegistratie`, `informatieobjecttype`, `vertrouwelijkheidaanduiding`,
and `inhoud` as a **download URL** (DRC allows returning a link to the
content). Fields we don't track (`lock`, `ondertekening`, `integriteit`) are
returned null — not fabricated.

**Verbs:**
- `create`: accepts base64 `inhoud` (per the standard) + metadata; decodes
  and writes the file, sets `bestandsomvang` from actual bytes, `versie: 1`,
  persists the OR object, returns 201 with the ZGW shape.
- `show`/`index`: OR reads mapped to the shape; `index` supports the
  standard `identificatie`/`bronorganisatie` query filters via the existing
  request-params parsing.
- `update` (PUT/PATCH semantics on the registered quintet): metadata update;
  when `inhoud` is supplied the file is overwritten (NC Files versioning
  retains the old bytes) and `versie` increments.
- `destroy`: deletes the OR object and the backing file (file already gone →
  still delete metadata, log a warning).
- `download` (new route `GET
  /api/drc/enkelvoudiginformatieobjecten/{id}/download`): streams the file
  with its stored content type via a `StreamResponse`/`FileDisplayResponse`.

**Route move:** the `documenten` resource URL changes from `api/drc` to
`api/drc/enkelvoudiginformatieobjecten`. The bare `api/drc` had no
conforming consumers (it 501'ed since introduction), so this is not a
breaking change in practice.

## D4 — What stays out

- BRC `besluitinformatieobjecten`, DRC `gebruiksrechten`, locks and
  `verzendingen`: not stubbed today, not promised — out of scope.
- Frontend rewiring of the documenten views onto the DRC endpoint: follow-up
  (the views currently use `/api/objects/documenten`; data continuity is a
  store-migration Phase-2 concern).
- ZTC `informatieobjecttypen` authoring UI: out of scope; `informatieobjecttype`
  is accepted as a reference value.

## Testing

Pure-backend REST: **PHPUnit** for the mappers/service (audit-trail mapping
table, zaakbesluit validation, `CaseDocumentService` with mocked
`IRootFolder`) and **Newman** for the contract (extend
`tests/integration/zaakafhandelapp.postman_collection.json`: zaakbesluiten
CRUD round-trip, audit-trail list after a zaak mutation + 405 on POST, DRC
create-with-inhoud → download byte equality → update-version → delete). No
Playwright — both touched specs carry the whole-spec
`@e2e exclude pure-backend REST controller spec` directive.
