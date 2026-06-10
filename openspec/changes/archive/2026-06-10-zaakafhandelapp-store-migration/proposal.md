# Proposal — zaakafhandelapp adopts `@conduction/nextcloud-vue` `useObjectStore`

## Why

The project memory rule **"Store pattern guidance — Do not use custom
stores; use Options API with `createObjectStore`"** flags every app
that ships a hand-rolled pinia object store as carrying the same
class of bug demonstrated by **decidesk #162**: when an app rolls
its own `useObjectStore`-shaped wrapper (without exposing
`fetchObject`, `getObject`, `getCachedObject`, etc.), the lib's
sub-resource plugins (`liveUpdatesPlugin`, `auditTrailsPlugin`,
`filesPlugin`, `relationsPlugin`) can't activate against it. The
runtime consequence is silent feature-loss: live updates simply
never propagate; the audit-trail tab loads empty; file widgets
fail to attach.

`zaakafhandelapp` currently exports **thirteen hand-rolled pinia
stores** (`berichtStore`, `klantStore`, `navigationStore`,
`rolStore`, `taakStore`, `zaakStore`, `zaakTypeStore`,
`searchStore`, `contactMomentStore`, `medewerkerStore`,
`resultaatStore`, `besluitStore`, `documentStore`) from
`src/store/store.js`, each defined in its own module under
`src/store/modules/`. Eleven of them call flat in-app PHP
controllers — `/index.php/apps/zaakafhandelapp/api/zrc/zaken`,
`/api/ztc/zaaktypen`, `/api/objects/besluiten`,
`/api/contactmomenten`, `/api/berichten`, `/api/klanten`,
`/api/medewerkers`, `/api/taken`, `/api/objects/rollen`,
`/api/objects/resultaten`, `/api/objects/documenten` — none of
which match the OR canonical shape
`/apps/openregister/api/objects/{register}/{schema}/{id}` that
the lib's `useObjectStore` expects.

This is the **hybrid data-layer** flagged in
`zaakafhandelapp-manifest-v1` design.md (lines 233–238) and
governed by ADR-024: the manifest renderer ships JSON pages that
declare `register: "zaakafhandelapp"` + per-schema slugs, but the
underlying data layer still answers via in-app controllers. Until
the controllers are either (a) rewritten to delegate to OR or
(b) replaced by real OR registers + schemas, the app cannot
collapse onto a single OR-backed store.

This change is **Phase 1** of a two-phase migration:

1. **Phase 1 (this change).** Adopt the lib's `useObjectStore`
   side-by-side with the legacy controller-backed stores. Boot the
   lib store with the manifest's 11 OR-shaped object types pointed
   at the canonical `/apps/openregister/api/objects` base URL.
   Re-export `useObjectStore` from `src/store/store.js` so
   manifest-driven pages, `CnIndexPage` / `CnDetailPage`, and the
   sub-resource plugins resolve a known-shape store. Legacy stores
   continue serving every existing Vue file unchanged.
2. **Phase 2 (follow-up).** Once OR registers + schemas exist for
   the eleven types — or the in-app controllers are rewritten to
   serve the OR canonical shape — retire the legacy stores
   per-entity and switch the surviving Vue files onto the lib
   store.

Phase 1 unblocks the lib's plugin ecosystem (live updates, audit,
files, relations, selection) for any consumer that opts into the
lib store, mirrors the decidesk PR #160 reference migration, and
satisfies the project-memory rule for the manifest-rendered
surface without churning ~80 legacy Vue files in a single PR.

## What Changes

- **ADD** the lib `useObjectStore` instance to the app's pinia
  ecosystem. Configure it once at boot with the canonical OR
  base URL (`/apps/openregister/api/objects` via
  `@nextcloud/router#generateUrl`).
- **ADD** an `initializeStores()` helper in `src/store/store.js`
  that registers the eleven manifest types
  (`zaak`, `taak`, `klant`, `medewerker`, `contactmoment`,
  `bericht`, `rol`, `zaaktype`, `besluit`, `resultaat`,
  `document`) against the `zaakafhandelapp` register. The helper
  is async and idempotent — safe to call from `main.js` before
  `new Vue()`.
- **ADD** a re-export of `useObjectStore` from
  `src/store/store.js` so consumers can `import { useObjectStore }
  from '../store/store.js'` rather than reaching into the lib
  directly. This matches the pattern used by decidesk PR #160.
- **CALL** `initializeStores()` from `src/main.js` before `$mount`,
  using the standard fire-and-forget pattern (registration is
  synchronous; the awaited future is reserved for Phase 2 when the
  helper will also `fetchSettings()`).
- **KEEP** every legacy store, every legacy Vue consumer, every
  legacy `apiEndpoint` URL, and every existing entity class
  unchanged. Phase 1 is **purely additive**.
- **DOCUMENT** the side-by-side pattern + the Phase 2 cutover
  plan in `design.md`.

## Impact

### Affected specs

- **NEW** `specs/zaakafhandelapp-store/spec.md` — declares the
  store-adoption requirements: lib-store presence, registered
  object types, base URL, re-export shape, side-by-side
  invariant, Phase 2 follow-up triggers.

### Affected code

- `src/store/store.js` — additive (re-export + boot helper).
- `src/main.js` — single new call to `initializeStores()`.
- No other files modified. No legacy stores or Vue consumers
  touched.

### Affected behaviour

- **Manifest pages now resolve a known-shape store.** Index /
  detail pages declared in `src/manifest.json` (Zaken, Taken,
  Klanten, etc.) can pass the lib store + an `objectType` slug
  to `CnIndexPage` and the form-dialog save path will work.
  Visible data still depends on Phase 2 (OR registers/schemas
  not yet seeded), but the store wiring is correct.
- **Live updates / audit / files / relations plugins activate**
  for any consumer that opts onto the lib store. Decidesk #162
  class of bug cannot occur for new code paths.
- **Legacy Vue files still work.** Zero behavioural regression
  for any of the ~80 files importing `zaakStore`, `taakStore`,
  etc. from `./store/store.js`.

### Citations

- Project memory: **"Store pattern guidance — Do not use custom
  stores; use Options API with `createObjectStore`"** (file
  `feedback_store-pattern.md`).
- Decidesk **issue #162** — canonical "live-updates plugin
  can't activate; `fetchObject` is missing" example for an
  app-rolled object store.
- Decidesk **PR #160** — reference manifest + store migration
  (commits `b5c88cd2`, `4b49bca1`, `ed34703c`, `50e4df7c`,
  `866ff132`).
- Manifest design: `openspec/changes/zaakafhandelapp-manifest-v1/design.md`
  lines 233–238 (hybrid data-layer note).
- ADR: `hydra/openspec/architecture/adr-024-app-manifest.md`.
- Lib API: `@conduction/nextcloud-vue@1.0.0-beta.13`
  `src/store/useObjectStore.js` (`createObjectStore`,
  `registerObjectType`, `configure`).
