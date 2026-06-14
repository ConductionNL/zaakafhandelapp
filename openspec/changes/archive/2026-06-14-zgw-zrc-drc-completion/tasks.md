# Tasks — zgw-zrc-drc-completion

## 1. Registry & schema groundwork

- [x] 1.1. Add `'enkelvoudiginformatieobject' => 'enkelvoudiginformatieobject'`
      to `ZGWRegistryService::SCHEMAS` + a
      `getEnkelvoudigInformatieObjectSchema()` getter (mirrors the existing
      getters; `zaakbesluit` and the DRC/BRC register slugs already exist).
- [~] 1.2. Verify the configured BRC register carries a `zaakbesluit` schema
      and the DRC register an `enkelvoudiginformatieobject` schema in the OR
      seed/configuration (admin settings register mapping). DEFERRED: the
      register/schema objects are provisioned per-instance through OpenRegister
      admin configuration (`{type}_register`/`{type}_schema` app-config keys),
      not committed app code; the controllers read them through the existing
      `ObjectService` config resolution. The slug + getter are in place (1.1);
      seeding is a deployment/admin task verified on the live instance.
- [~] 1.3. The `enkelvoudiginformatieobject` schema includes a `fileId`
      (Nextcloud file id) property plus the ZGW metadata fields listed in
      REQ-004. DEFERRED with 1.2: the controller persists `fileId` + the ZGW
      metadata onto the object; the schema property definition lives in the
      per-instance OpenRegister schema, not in committed app code.

## 2. ZaakBesluitenController (ZRC)

- [x] 2.1. Inject `ObjectService` + `ZGWRegistryService`; drop the now-unused
      `IAppConfig` if nothing else needs it.
- [x] 2.2. `index(string $zaak_uuid)`: fetch `zaakbesluit` objects filtered
      on `zaak == $zaak_uuid`; map each to `{url, uuid, zaak, besluit}`
      (absolute URLs via `IURLGenerator`).
- [x] 2.3. `show(string $zaak_uuid, string $id)`: fetch by id, 404 when
      missing OR when the object's `zaak` differs from the routed
      `$zaak_uuid` (IDOR guard).
- [x] 2.4. `create(string $zaak_uuid)`: strip client `id`; 404 when the zaak
      does not exist; 400 when the `besluit` reference does not resolve
      (local besluit uuid → OR read; absolute external URL → accepted);
      persist with `zaak = $zaak_uuid`; return 201 + ZGW shape.
- [x] 2.5. `update(...)`: only `besluit` is mutable; `zaak` stays the routed
      value; same validations as create.
- [x] 2.6. `destroy(...)`: delete the relation object only; 404 when absent.
      Confirm the referenced besluit is untouched.
- [x] 2.7. Update the controller docblock (no longer a stub) and `@spec` tags
      to `openspec/specs/zgw-zaak-management/spec.md#REQ-006`.

## 3. ZaakAuditTrailController (ZRC)

- [x] 3.1. Inject `ObjectService`; implement `index(string $zaak_uuid)` via
      `ObjectService::getAuditTrail('zaken', $zaak_uuid)`.
- [x] 3.2. Add the OR-AuditTrail → ZGW-Audittrail mapper (per the design.md
      field table) — small dedicated class or private method; cover `actie`
      mapping (create/update/delete) and `wijzigingen.oud`/`nieuw`.
- [x] 3.3. `show(...)`: return the single mapped entry by audit uuid, scoped
      to the routed zaak; 404 otherwise.
- [x] 3.4. `create`/`update`/`destroy`: return 405 Method Not Allowed with an
      `Allow: GET` header (audit trail is read-only per ZRC); keep the 401
      unauthenticated guard on all verbs.
- [x] 3.5. Update docblocks + `@spec` tags to
      `openspec/specs/zgw-zaak-management/spec.md#REQ-007`.

## 4. DRC: CaseDocumentService + DocumentenController

- [x] 4.1. New `lib/Service/CaseDocumentService.php`: per-zaak folder
      resolution/creation under `Zaakdocumenten/{zaak}` via
      `IRootFolder::getUserFolder()`; `writeDocument()` (base64 decode →
      file, returns fileId + size), `replaceContent()` (overwrite, NC
      versioning retains old bytes), `readStream()`, `deleteDocument()`.
      All failures wrapped in typed exceptions (no silent catch-return-null).
- [x] 4.2. `DocumentenController::create()`: validate required ZGW fields +
      base64 `inhoud`; write file via the service; persist OR metadata
      (DRC register / `enkelvoudiginformatieobject` schema) with `fileId`,
      `versie` 1, real `bestandsomvang`; 201 + ZGW shape.
- [x] 4.3. `index()`/`show()`: OR reads mapped to the ZGW shape with `inhoud`
      as the download URL; support `identificatie`/`bronorganisatie` filters
      via the existing request-params parsing; untracked fields null.
- [x] 4.4. `update()`: metadata update; when `inhoud` present, replace
      content + increment `versie`.
- [x] 4.5. `destroy()`: delete metadata + backing file; metadata-only delete
      with a logged warning when the file is already gone; 404 when absent.
- [x] 4.6. `download(string $id)`: stream the bytes
      (`StreamResponse`/`FileDisplayResponse`) with stored content type; 404
      when metadata or file is missing.
- [x] 4.7. Update docblocks (no longer a stub) + `@spec` tags to
      `openspec/specs/zgw-related-resources/spec.md#REQ-001` / `#REQ-004`.

## 5. Routes

- [x] 5.1. `appinfo/routes.php`: change the `documenten` resource URL from
      `api/drc` to `api/drc/enkelvoudiginformatieobjecten`.
- [x] 5.2. Add `['name' => 'documenten#download', 'url' =>
      '/api/drc/enkelvoudiginformatieobjecten/{id}/download', 'verb' => 'GET']`.
- [x] 5.3. Remove the stale 501/issue-#268 stub comments for zaakBesluiten,
      zaakAuditTrail and documenten.
- [x] 5.4. Every new/changed controller method carries an auth annotation
      (gate-5) and a reachable route entry (gate-14).

## 6. Specs & annotations

- [x] 6.1. On archive, sync the deltas into
      `openspec/specs/zgw-zaak-management/spec.md` (REQ-005 modified,
      REQ-006/REQ-007 added) and
      `openspec/specs/zgw-related-resources/spec.md` (REQ-001 modified,
      REQ-004 added) — extend, do not duplicate, the retrofit specs.
- [x] 6.2. Sweep remaining `@spec` references: ZaakBesluiten/ZaakAuditTrail
      methods currently point at REQ-005 generically — retag to the specific
      new requirements.

## 7. Tests

- [x] 7.1. PHPUnit (tests/Unit): audit-trail mapper (field table incl. actie
      mapping + wijzigingen), zaakbesluit create validation (missing zaak →
      404 path, bad besluit → 400 path, cross-zaak show → 404),
      `CaseDocumentService` with mocked `IRootFolder` (write/replace/delete,
      base64 round-trip, size derivation).
- [~] 7.2. Newman: extend
      `tests/integration/zaakafhandelapp.postman_collection.json` with
      ZRC zaakbesluiten CRUD round-trip, audit-trail list after a zaak
      create+update (assert ZGW shape + non-empty wijzigingen) and POST → 405,
      DRC create-with-inhoud → download byte-equality → update bumps versie →
      delete → 404. Runs via `tests/integration/run-newman.sh`. DONE: a "7. ZGW
      ZRC/DRC completion" folder (10 requests) covers the 2xx happy paths, the
      405 read-only audit write (Allow: GET), the ZGW envelope/shape, DRC
      create→download byte-equality→delete, and unauth→401; the stale
      documenten-501 assertion was updated. The live run against a configured
      instance is deferred (needs the per-instance register/schema config of 1.2).
- [x] 7.3. Playwright: none — both touched specs are whole-spec
      `@e2e exclude pure-backend REST controller spec` (API contract lives in
      Newman per testing policy).

## 8. Quality & release

- [~] 8.1. `composer check:strict` clean (PHPCS, PHPMD, Psalm, PHPStan); fix
      any pre-existing issues encountered in the touched files in the same
      batch. PARTIAL: all 24 Hydra mechanical gates are green for the diff and
      `php -l` passes on every changed file; the full `check:strict` (PHPCS
      arcanist ruleset + PHPMD + Psalm + PHPStan) is run by CI on the PR — the
      local php 8.2 / partial dev-tool install here cannot run the OR-stubbed
      Psalm/PHPStan reliably (see MEMORY: local ocp stub is stale).
- [x] 8.2. `grep -rn 'NOT_IMPLEMENTED\|501' lib/Controller/` returns no data
      endpoint hits; `grep -rn '#268' appinfo/ lib/` empty.
- [x] 8.3. Bump `appinfo/info.xml` `<version>` (API surface change).
- [x] 8.4. Close/cross-reference issue #268 in the PR description.
