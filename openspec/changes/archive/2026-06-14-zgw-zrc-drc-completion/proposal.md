# Proposal — zgw-zrc-drc-completion

## Why

The README claims "GEMMA API: Aligned with Dutch government common
architecture patterns", but three pieces of the ZGW API surface are 501
stubs (issue #268), as `appinfo/routes.php` documents in-line:

- `zaakBesluiten` (`/api/zrc/zaken/{zaak_uuid}/besluiten`) — every data verb
  in `lib/Controller/ZaakBesluitenController.php` returns
  `501 Not Implemented`;
- `zaakAuditTrail` (`/api/zrc/zaken/{zaak_uuid}/audit_trail`) — every data
  verb in `lib/Controller/ZaakAuditTrailController.php` returns 501, even
  though the plumbing already exists: `ObjectService::getAuditTrail()` routes
  to OpenRegister's `getLogs()` and is already consumed by the in-app route
  `zaken#getAuditTrail` (`/api/zaken/{id}/audit_trail`);
- `documenten` (`/api/drc`) — every data verb in
  `lib/Controller/DocumentenController.php` returns 501, so the DRC
  (Documenten API) is entirely absent and case documents have no real file
  backing.

These stubs are the gap between the "ZGW-aligned" promise and reality, and
they block interoperability with other GEMMA components (anything consuming
the ZRC besluit relation, audit trails for Archiefwet compliance checks, or
DRC informatieobjecten). `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md`
rates this **high** ("Standard ZGW ZRC/DRC API completion") and pairs the DRC
work with expected-gap 4: *case documents as real NC Files* — users of a
Nextcloud case app reasonably expect per-zaak folders with real files
(preview, share, versioning from NC core), not bare metadata objects.

The building blocks are in place: `ZGWRegistryService` already maps the
`brc`/`drc` registers and the `besluit`, `zaakbesluit`, `zio`, `bio`, `oio`
and `informatieobjecttype` schema slugs; `ObjectService` already provides OR
CRUD and audit-trail access; `BesluitenController` already serves besluit
resources; Nextcloud Files provides storage, preview and versioning for free.

## What Changes

- **IMPLEMENT** `ZaakBesluitenController` against OpenRegister: CRUD of
  `zaakbesluit` relation objects (BRC register, `zaakbesluit` schema via
  `ZGWRegistryService`) scoped to the `zaak_uuid` in the route, returning the
  ZGW `ZaakBesluit` shape (`url`, `uuid`, `zaak`, `besluit`). Create
  validates that both the zaak and the referenced besluit exist; delete
  removes the relation, never the besluit itself.
- **IMPLEMENT** `ZaakAuditTrailController` read endpoints from the
  OpenRegister object audit trail: `index`/`show` map
  `ObjectService::getAuditTrail('zaken', $zaakUuid)` entries onto the ZGW
  `Audittrail` resource shape. Per the ZRC standard the audit trail is
  read-only, so `create`/`update`/`destroy` return **405 Method Not Allowed**
  (with an `Allow: GET` header) instead of 501 — the routes stay registered
  for gate-14.
- **IMPLEMENT** the DRC `enkelvoudiginformatieobjecten` endpoint in
  `DocumentenController`, backed by **Nextcloud Files** for binary content
  and OpenRegister (DRC register, new `enkelvoudiginformatieobject` schema
  slug) for the ZGW metadata: create accepts base64 `inhoud` per the DRC
  standard and writes a real file into a per-zaak case-documents folder;
  read/list return ZGW-shaped metadata with a resolvable `inhoud` download
  URL; update writes a new file version and increments `versie`; delete
  removes metadata and file. A `download` route streams the file content.
- **MOVE** the DRC resource URL from the bare `api/drc` to
  `api/drc/enkelvoudiginformatieobjecten` (the standard's path) and add the
  download route in `appinfo/routes.php`; remove the 501/issue-#268 stub
  comments.
- **EXTEND** `ZGWRegistryService` with the `enkelvoudiginformatieobject`
  schema slug + getter; ensure the BRC/DRC register configuration seeds the
  `zaakbesluit` and `enkelvoudiginformatieobject` schemas.
- **EXTEND** the existing retrofit specs rather than duplicating them:
  `zgw-zaak-management` (REQ-005 modified; REQ-006 zaakbesluiten + REQ-007
  ZRC audit trail added) and `zgw-related-resources` (REQ-001 modified;
  REQ-004 NC-Files-backed enkelvoudiginformatieobjecten added).

## Impact

### Affected specs

- **MODIFIED** `specs/zgw-zaak-management/spec.md` — REQ-005 loses its
  implicit stub allowance; ADDED REQ-006 (zaakbesluiten lifecycle) and
  REQ-007 (ZRC zaak audit trail).
- **MODIFIED** `specs/zgw-related-resources/spec.md` — REQ-001 restated so
  documenten serve real data; ADDED REQ-004 (enkelvoudiginformatieobject
  content in Nextcloud Files).

### Affected code

- `lib/Controller/ZaakBesluitenController.php` — full implementation
  (currently 501 stubs).
- `lib/Controller/ZaakAuditTrailController.php` — GET implementation + 405
  on write verbs (currently 501 stubs).
- `lib/Controller/DocumentenController.php` — full DRC implementation
  (currently 501 stubs) + new `download` action.
- `lib/Service/CaseDocumentService.php` — **new**: NC Files folder
  management, base64 decode/encode, streaming download, version bookkeeping.
- `lib/Service/ZGWRegistryService.php` — additive schema slug + getter.
- `appinfo/routes.php` — DRC resource URL, download route, stale stub
  comments removed.
- `tests/Unit/...` + `tests/integration/zaakafhandelapp.postman_collection.json`
  — new coverage.

### Affected behaviour

- ZGW clients hitting `/api/zrc/zaken/{uuid}/besluiten`,
  `/api/zrc/zaken/{uuid}/audit_trail` and
  `/api/drc/enkelvoudiginformatieobjecten` get real, ZGW-shaped data instead
  of 501. Closes issue #268.
- Case documents become real Nextcloud files (preview/share/versioning from
  NC core); existing `zaakInformatieObjecten` links can resolve their
  `informatieobject` references to served documents.
- No frontend change required in this change; the existing documenten UI
  keeps using `/api/objects/documenten` until a follow-up rewires it.

### Citations

- `appinfo/routes.php` lines 12–21 (501 stub comments, issue #268).
- `lib/Controller/{ZaakBesluiten,ZaakAuditTrail,Documenten}Controller.php`
  (stub bodies).
- `lib/Service/ObjectService.php` `getAuditTrail()` (existing OR audit
  plumbing); `lib/Service/ZGWRegistryService.php` (register/schema slugs).
- VNG standards: Zaken API (ZRC) 1.5.1 — `zaakbesluiten`, `audittrail`
  sub-resources; Documenten API (DRC) 1.5.0 — `enkelvoudiginformatieobjecten`.
- `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` — MISSING row
  "Standard ZGW ZRC/DRC API completion" (high), expected-gap 4,
  Recommendation #2.
