# Design — zaakafhandelapp store migration (Phase 1, side-by-side)

## Context

`zaakafhandelapp` lands in this PR with a freshly-merged manifest
renderer (`zaakafhandelapp-manifest-v1`, PR #189). The manifest
declares 11 OR-shaped object types under register
`zaakafhandelapp`, but the app's actual data layer is **eleven
flat in-app PHP controllers**, none of which match the lib's OR
URL contract:

| Manifest slug    | Legacy controller endpoint                                          | Schema-shape? |
| ---              | ---                                                                 | ---           |
| `zaak`           | `/index.php/apps/zaakafhandelapp/api/zrc/zaken`                     | flat          |
| `zaaktype`       | `/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen`                 | flat          |
| `besluit`        | `/index.php/apps/zaakafhandelapp/api/objects/besluiten`             | flat          |
| `resultaat`      | `/index.php/apps/zaakafhandelapp/api/objects/resultaten`            | flat          |
| `rol`            | `/index.php/apps/zaakafhandelapp/api/objects/rollen`                | flat          |
| `document`       | `/index.php/apps/zaakafhandelapp/api/objects/documenten`            | flat          |
| `bericht`        | `/index.php/apps/zaakafhandelapp/api/berichten`                     | flat          |
| `klant`          | `/index.php/apps/zaakafhandelapp/api/klanten`                       | flat          |
| `medewerker`     | `/index.php/apps/zaakafhandelapp/api/medewerkers`                   | flat          |
| `taak`           | `/index.php/apps/zaakafhandelapp/api/taken`                         | flat          |
| `contactmoment`  | `/index.php/apps/zaakafhandelapp/api/contactmomenten`               | flat          |

The lib's `useObjectStore._buildUrl` is hard-coded to
`${baseUrl}/${register}/${schema}/${id}` — there is no consumer
hook to override the URL builder per-type. So we cannot point the
lib's store at the legacy controllers without forking the lib's
URL contract.

## Migration patterns considered

### A. Full migration (rejected)

Mass-rewrite every legacy controller to serve the OR canonical
shape, then point a single lib `useObjectStore` at them. **Out of
scope for this change.** The controllers are 11 separate PHP
classes with different shapes (ZRC, ZTC, custom); rewriting them
is a multi-week effort and out of scope for a frontend-store
migration. ADR-024 explicitly permits the hybrid layer until the
controllers are reworked.

### B. Phased / thin-wrap (rejected for this app)

Wrap each legacy store inside a lib-shaped facade so consumers
code against `useObjectStore` even when the underlying calls hit
the legacy controllers. **Rejected** because the legacy stores
expose entity-specific actions
(`refreshZakenList`, `getZaak`, `saveZaak`, `deleteZaak`,
`setZaakItem`, `setAuditTrailItem`, `setZakenList`) that don't
fit the lib's generic `(type, id)` API — a faithful wrapper would
hide enough surface that consumers would still need to bypass it
for the entity-specific cases. The thin wrap would be a
maintenance liability with no net win.

### C. Side-by-side (chosen)

Add the lib's `useObjectStore` to the app's pinia ecosystem
**alongside** the existing 13 legacy stores. Pre-register the 11
manifest types against the canonical OR base URL. Legacy stores
continue serving every existing Vue file. New code (manifest
pages, lib components, sub-resource plugins) opts onto the lib
store. The two stores coexist by Pinia store-id segregation
(legacy stores have IDs like `'zaken'`, `'taken'`; the lib store
has ID `'conduction-objects'`).

This is the **explicit Phase 1** of a two-phase migration. Phase
2 retires the legacy stores once OR registers + schemas exist for
the eleven types, or once the controllers are rewritten to OR
shape.

## Implementation

### `src/store/store.js`

Keep the existing 13 legacy `*Store` exports verbatim. Add three
new pieces:

1. Import the lib's `useObjectStore` from
   `@conduction/nextcloud-vue`.
2. Re-export it as a named export so consumers can do
   `import { useObjectStore } from '../store/store.js'`.
3. Export an `initializeStores()` async helper that:
   - Calls `objectStore.configure({ baseUrl: generateUrl('/apps/openregister/api/objects') })`.
   - Calls `objectStore.registerObjectType(slug, schema, register)` for each of the 11 types, with `register = 'zaakafhandelapp'` and `schema` matching the manifest slugs.
   - Returns the store instance for chaining.

The helper is **idempotent**. `registerObjectType` is a setter on
plain pinia state; calling it twice for the same slug overwrites
the registry entry (same value), no harm done.

### `src/main.js`

Add a single call to `initializeStores()` before `new Vue(...)`.
Mirror the existing `tryLoadTranslations()` pattern: fire-and-forget,
don't block the mount on registration. Registration is synchronous
in Phase 1 (no fetches), so the await is reserved for Phase 2.

### Why no manifest changes

The manifest already declares `register: "zaakafhandelapp"` and
per-page `schema: "<slug>"`. The `CnPageRenderer` spreads
`page.config` as props onto the dispatched page component. To
hand a *store* to `CnIndexPage` we'd need to either:

- (a) add a `store: { type: Object, default: null }` reference
  to every index page in the manifest — but pinia stores aren't
  JSON-serializable,
- (b) add a `useStore` setup helper inside the lib's `CnIndexPage`
  that auto-resolves the lib's default store — that's a lib
  change, not an app change,
- (c) wrap the manifest pages locally with a custom-component
  shim that injects the store — adds churn without immediate
  payoff.

Phase 1 deliberately stops at *making the lib store available*.
Wiring it into the manifest renderer is a Phase 2 concern, joined
with the Phase 2 controller cutover.

## Boundary lines

- **In scope (Phase 1)**:
  - `src/store/store.js` — additive: lib re-export + boot helper.
  - `src/main.js` — one new call.
  - `openspec/changes/zaakafhandelapp-store-migration/` — change docs.

- **Out of scope (deferred to Phase 2 or other changes)**:
  - Replacing legacy controller endpoints with OR-shaped routes.
  - Seeding OR registers + schemas for `zaakafhandelapp`.
  - Migrating any of the ~80 Vue files that import legacy
    `zaakStore`, `taakStore`, etc.
  - Wiring the lib store into manifest index/detail pages.
  - Deleting any legacy `src/store/modules/*.ts|.js` file.

## Risks & mitigations

1. **Pinia store-id collision.** The lib's store ID is
   `'conduction-objects'`. None of the 13 legacy stores use that
   ID (verified: `'berichten'`, `'klanten'`, `'navigation'`,
   `'rol'`, `'taken'`, `'zaken'`, `'zaakTypes'`, `'search'`,
   `'contactmomenten'`, `'medewerkers'`, `'resultaat'`,
   `'besluit'`, `'documenten'`). No collision risk.
2. **Bundle-size delta.** The lib's `useObjectStore` is already
   imported transitively via `CnAppRoot` / `CnIndexPage` — adding
   a direct import does not pull new code into the bundle.
3. **Boot ordering.** `initializeStores()` MUST run after
   `Vue.use(PiniaVuePlugin)` and after `pinia` is constructed
   but before `new Vue({pinia, ...}).$mount`. The lib's store
   reads `state.objectTypeRegistry` lazily on first use, so a
   slightly-late registration would still work, but we keep it
   synchronous-before-mount to match decidesk's reference order.
4. **Legacy stores keep diverging.** Phase 1 freezes the
   side-by-side state; nothing prevents future code from adding
   to the legacy stores. The Phase 2 follow-up change should be
   filed before the next round of feature work to keep the
   migration tractable.

## Phase 2 triggers

Phase 2 should land when **any** of these are true:

1. OR registers + schemas exist for at least one of the 11
   `zaakafhandelapp` types, with seeded data.
2. An in-app controller is rewritten to serve the OR canonical
   shape (e.g. ZakenController moves to
   `/apps/openregister/api/objects/zaakafhandelapp/zaak`).
3. A new feature requires the lib's sub-resource plugins
   (live updates, audit trails, files) on a legacy store path —
   forcing an OR-shape migration of that one type.

When Phase 2 lands, the corresponding legacy store + Vue files
get retired per-entity, the manifest's index/detail pages bind
to the lib store, and the controller's PHP class is deleted (or
delegated to OR). The Phase 1 boot wiring stays intact — only
the legacy stores and the in-app controllers retire.

## Citations

- Project memory: `feedback_store-pattern.md` — "Do not use
  custom stores; use Options API with `createObjectStore`".
- Decidesk **issue #162** — canonical missing-`fetchObject` bug.
- Decidesk **PR #160** — reference manifest + store migration.
- Manifest design (this app): lines 233–238 of
  `openspec/changes/zaakafhandelapp-manifest-v1/design.md`.
- ADR: `hydra/openspec/architecture/adr-024-app-manifest.md`.
- Lib: `@conduction/nextcloud-vue@1.0.0-beta.13`
  `src/store/useObjectStore.js`.
