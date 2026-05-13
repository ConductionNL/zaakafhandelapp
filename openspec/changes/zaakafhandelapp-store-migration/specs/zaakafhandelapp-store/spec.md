# Specification — `zaakafhandelapp-store` (Phase 1, side-by-side)

## ADDED Requirements

### Requirement: lib `useObjectStore` is the canonical object store

`zaakafhandelapp` SHALL adopt
`@conduction/nextcloud-vue`'s `useObjectStore` as the canonical
generic object store for any code path needing
register-and-schema-based CRUD, sub-resource plugins (live
updates, audit trails, files, relations), or manifest-driven
index/detail pages.

#### Scenario: lib store is exported from the app's store barrel

- **GIVEN** a Vue file in `src/`
- **WHEN** it imports `useObjectStore` from `'../store/store.js'`
  (or the equivalent relative path)
- **THEN** the import SHALL resolve to the lib's `useObjectStore`
  re-exported from `@conduction/nextcloud-vue`
- **AND** the imported function SHALL return a pinia store with
  the `'conduction-objects'` Pinia store ID
- **AND** the store SHALL expose `fetchCollection`, `fetchObject`,
  `saveObject`, `deleteObject`, `getCollection`, `getObject`,
  `getCachedObject`, `isLoading`, `getError`, `getPagination`,
  `getSchema`, `getRegister`, `getFacets`, `setSearchTerm`,
  `clearError`, `resolveReferences`, `registerObjectType`,
  `unregisterObjectType`, `configure`, `createObjectTypeSlug`
  (the lib's documented base API).

### Requirement: object types are registered at boot

`zaakafhandelapp` SHALL register the eleven manifest-declared
object types against the lib store at app boot. The
registrations SHALL use the canonical OR base URL and the
`zaakafhandelapp` register slug.

#### Scenario: boot helper registers the eleven types

- **GIVEN** the app boots and pinia is initialised
- **WHEN** `initializeStores()` runs (synchronously or as a
  fire-and-forget call from `src/main.js`)
- **THEN** `objectStore.configure(...)` SHALL be called with
  `baseUrl: generateUrl('/apps/openregister/api/objects')`
- **AND** `objectStore.registerObjectType(slug, schema, register)`
  SHALL be called for each of `zaak`, `taak`, `klant`,
  `medewerker`, `contactmoment`, `bericht`, `rol`, `zaaktype`,
  `besluit`, `resultaat`, `document` with
  `schema = slug` and `register = 'zaakafhandelapp'`
- **AND** `objectStore.objectTypes` SHALL include all eleven
  slugs
- **AND** subsequent calls to `initializeStores()` SHALL be
  idempotent (no harm, no thrown errors).

### Requirement: legacy stores remain available side-by-side

Phase 1 SHALL be additive. Every legacy store currently
exported from `src/store/store.js` SHALL continue to be exported
unchanged. Every Vue file that imports a legacy store SHALL
continue to function identically.

#### Scenario: legacy exports unchanged

- **GIVEN** the post-migration `src/store/store.js`
- **WHEN** a consumer imports any of the thirteen legacy stores
  (`berichtStore`, `klantStore`, `navigationStore`, `rolStore`,
  `taakStore`, `zaakStore`, `zaakTypeStore`, `searchStore`,
  `contactMomentStore`, `medewerkerStore`, `resultaatStore`,
  `besluitStore`, `documentStore`)
- **THEN** the import SHALL resolve to the same pinia store
  instance the consumer received before this change
- **AND** the legacy store's `apiEndpoint` constants and
  actions SHALL remain unchanged.

#### Scenario: no Pinia store-id collision

- **GIVEN** the post-migration store ecosystem
- **WHEN** any consumer instantiates the lib store and any
  legacy store within the same pinia
- **THEN** there SHALL be no Pinia store-id collision (the lib
  uses `'conduction-objects'`; the legacy stores use IDs
  `'berichten'`, `'klanten'`, `'navigation'`, `'rol'`, `'taken'`,
  `'zaken'`, `'zaakTypes'`, `'search'`, `'contactmomenten'`,
  `'medewerkers'`, `'resultaat'`, `'besluit'`, `'documenten'`).

### Requirement: Phase 2 cutover triggers are documented

The change SHALL document the explicit triggers for Phase 2 (the
follow-up that retires the legacy stores). Phase 2 SHALL NOT be
attempted in this change.

#### Scenario: Phase 2 trigger list is normative

- **GIVEN** the design.md of the
  `zaakafhandelapp-store-migration` change
- **WHEN** a future agent decides whether to file the Phase 2
  follow-up
- **THEN** the design.md SHALL list the trigger conditions:
  (a) OR registers + schemas seeded for any of the 11 types,
  (b) any in-app controller rewritten to OR canonical shape,
  (c) any feature requiring lib sub-resource plugins on a
  legacy store path
- **AND** matching any one trigger SHALL be sufficient grounds to
  schedule Phase 2 for that entity.
