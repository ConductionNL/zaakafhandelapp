# Tasks — zaakafhandelapp store migration (Phase 1)

## 1. Wire the lib store

- [ ] 1.1. Import `useObjectStore` from `@conduction/nextcloud-vue`
      in `src/store/store.js`.
- [ ] 1.2. Re-export `useObjectStore` from `src/store/store.js`
      alongside the existing thirteen legacy `*Store` exports.
- [ ] 1.3. Add `initializeStores()` async helper to
      `src/store/store.js`. The helper:
      - Calls
        `objectStore.configure({ baseUrl: generateUrl('/apps/openregister/api/objects') })`.
      - Calls `objectStore.registerObjectType(slug, schema, register)`
        for each manifest object type, with `register =
        'zaakafhandelapp'` and `schema = slug`.
      - Returns the store instance.
- [ ] 1.4. Object types to register (eleven, all under register
      `zaakafhandelapp`): `zaak`, `taak`, `klant`, `medewerker`,
      `contactmoment`, `bericht`, `rol`, `zaaktype`, `besluit`,
      `resultaat`, `document`. Use the slugs verbatim (no
      pluralisation) — these match `src/manifest.json`.

## 2. Bootstrap from main.js

- [ ] 2.1. Import `initializeStores` from `./store/store.js` in
      `src/main.js`.
- [ ] 2.2. Call `initializeStores()` after `Vue.use(PiniaVuePlugin)`
      and after `pinia` is imported, but before `new Vue(...).$mount`.
- [ ] 2.3. Use the existing `tryLoadTranslations`-style
      fire-and-forget pattern — don't await the helper at boot.

## 3. Specs

- [ ] 3.1. Add `specs/zaakafhandelapp-store/spec.md` with the
      requirements declared in the proposal.

## 4. Validation

- [ ] 4.1. `npx eslint src/store/store.js src/main.js` — clean.
- [ ] 4.2. `node tests/validate-manifest.js` — manifest still
      validates against the lib schema (no manifest change in
      this PR; should still pass as a sanity check).
- [ ] 4.3. `npx webpack --mode production` — build succeeds.
- [ ] 4.4. `npx jest --silent src/store` — existing store
      module tests still pass; no new test needed for Phase 1
      (the boot helper is exercised by the build and by Phase 2
      consumer adoption).

## 5. Documentation

- [ ] 5.1. Cross-link from
      `openspec/changes/zaakafhandelapp-manifest-v1/design.md`
      open-question #3 (data layer) — _not in this PR's scope_;
      noted for the Phase 2 follow-up so the manifest design and
      store design stay coherent.
