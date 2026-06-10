# Tasks — Zaakafhandelapp manifest v1: JSON manifest renderer adoption

## 1. Per-page mapping decision

- [x] 1.1 Walk every route in `src/router/router.ts` + `src/views/componentMapping.js` (13 logical resource pages plus dashboard, settings, search, audit). For each, read the corresponding `src/views/<area>/*Index.vue` to understand current shape.
- [x] 1.2 Decide each page's target type per the `design.md` mapping table: 12 `index`, 12 `detail`, 1 `dashboard`, 1 `settings`, 2 `custom`.
- [x] 1.3 Document genuine exceptions vs lib gaps vs migration cost in `design.md`'s "Custom-fallback inventory" section.

## 2. Manifest authoring

- [x] 2.1 Create `src/manifest.json`. Set `version: "1.0.0"`, `dependencies: ["openregister"]`.
- [x] 2.2 Author `menu[]` covering the same 11 entries today's `MainMenu.vue` shows (Dashboard, Cases, Tasks, Customers, Employees, Contact moments, Messages, Roles, Search → main; Case types, Audit trail, Documentation, Settings → settings).
- [x] 2.3 Author `pages[]` with 12 `type: "index"` + 12 `type: "detail"` + 1 `type: "dashboard"` + 1 `type: "settings"` + 2 `type: "custom"` = 28 entries.
- [x] 2.4 For each `type: "index"` page, declare `config.{ register, schema, columns, sidebar }`. Use `register: "zaakafhandelapp"` and per-resource schema slugs.
- [x] 2.5 For each `type: "detail"` page, declare `config.{ register, schema, sidebarTabs }`. Tab inventory per `design.md`.
- [x] 2.6 For `Dashboard`, declare `config.{ widgets, layout }`. Source widgets from existing `*Widget.vue` files.
- [x] 2.7 For `Settings`, declare `config.{ saveEndpoint, sections: [{ component: "SettingsForm" }] }` mirroring `manifest-settings-rich-sections`.
- [x] 2.8 For `type: "custom"` pages (`Search`, `AuditTrail`), preserve `component:` field referencing names registered in `customComponents.js`.

## 3. App shell rewrite (Tier-4 adoption)

- [x] 3.1 Rewrite `src/main.js` mirroring decidesk `b5c88cd2` + `50e4df7c` + `866ff132`:
    - Import `CnPageRenderer`, `defaultPageTypes`, `registerIcons`, `registerTranslations`.
    - Build router via `routesFromManifest(manifest)`; each page becomes one route, name = `page.id`, component = shallow-cloned `CnPageRenderer`, `props: true` when route contains `:`.
    - Fire-and-forget translation load (`tryLoadTranslations()`); never block mount on it.
    - Pass shallow copies of `defaultPageTypes` + `customComponents` to `<App>` as props (Vue.extend `_Ctor` cache fix).
    - Mount on `#content` unconditionally.
- [x] 3.2 Rewrite `src/App.vue` mirroring decidesk `4b49bca1`:
    - Mount `<CnAppRoot :manifest :customComponents :pageTypes :appId :translate :permissions>`.
    - `#sidebar` slot wires single host `<CnObjectSidebar>` driven by reactive `objectSidebarState` data + `provide()`.
    - Keep `Modals` + `Dialogs` mounts inside `App.vue` alongside `<CnAppRoot>` (cross-cutting modal hosts that are not part of the manifest).
- [x] 3.3 Add `src/customComponents.js` exporting:
    - `SearchView` (re-exported from `views/search/SearchIndex.vue`).
    - `AuditTrailView` (new placeholder under `src/views/audit/AuditTrailView.vue`).
    - `SettingsForm` (re-exported from `views/settings/Settings.vue`).
    - 7 stub tabs for `ZaakDetail`: `ZaakTakenTab`, `ZaakRollenTab`, `ZaakDocumentenTab`, `ZaakBesluitenTab`, `ZaakBerichtenTab`, `ZaakResultatenTab`, `ZaakStatussenTab`.
- [x] 3.4 Add `src/router/index.js` — manifest-driven router builder (replaces `src/router/router.ts` for new code paths). Catch-all redirect `*` → `/` preserved.

## 4. Library + build wiring

- [x] 4.1 Bump `package.json` `@conduction/nextcloud-vue` to `^1.0.0-beta.12`. (Lockfile resolves to `1.0.0-beta.13`.)
- [x] 4.2 Add `webpack.config.js` alias for `@nextcloud/axios$` mirroring decidesk `ed34703c`. Bypass the package's `exports` gate for `@nextcloud/vue`'s CJS `require('@nextcloud/axios')`.
- [x] 4.3 Mirror `l10n/en.json` to `l10n/en_US.json`. Lib's `loadTranslations` looks for the locale-with-region first.
- [x] 4.4 Bump `appinfo/info.xml` `<version>` from `0.1.31` to `0.2.0`.

## 5. Validator script

- [x] 5.1 Add `tests/validate-manifest.js` mirroring decidesk template — Ajv 2020 + ajv-formats, schema lookup order: env var → `node_modules/@conduction/nextcloud-vue/src/schemas/app-manifest.schema.json` → sibling worktree → fallbacks. Exit 0 on success, 1 on failure (with human-readable error list).
- [x] 5.2 Run `node tests/validate-manifest.js`. Confirm zero schema errors.
- [x] 5.3 Document re-run command in this file (Section 5).
    - Re-run: `node tests/validate-manifest.js`

## 6. Stub authoring (per-tab placeholders)

- [x] 6.1 Create `src/components/tabs/` directory.
- [x] 6.2 Author 7 stub tab components for `ZaakDetail` (`ZaakTakenTab`, `ZaakRollenTab`, `ZaakDocumentenTab`, `ZaakBesluitenTab`, `ZaakBerichtenTab`, `ZaakResultatenTab`, `ZaakStatussenTab`). Each is a thin `<template>` rendering `CnNoteCard` placeholder text plus a `TODO` header comment pointing at `tasks 3.3` follow-up implementation.
- [x] 6.3 Author `src/views/audit/AuditTrailView.vue` placeholder with `CnNoteCard` "Audit trail coming soon" message.

## 7. Spec artifacts

- [x] 7.1 `openspec/changes/zaakafhandelapp-manifest-v1/proposal.md` — written.
- [x] 7.2 `openspec/changes/zaakafhandelapp-manifest-v1/design.md` — written.
- [x] 7.3 `openspec/changes/zaakafhandelapp-manifest-v1/tasks.md` — this file.
- [x] 7.4 `openspec/changes/zaakafhandelapp-manifest-v1/specs/zaakafhandelapp-manifest-v1/spec.md` — Requirements REQ-ZMV1-1..N.

## 8. Validation + commit

- [x] 8.1 Run `node tests/validate-manifest.js` and confirm zero schema errors.
- [x] 8.2 Run `npx eslint src/manifest.json src/main.js src/App.vue src/router/index.js src/customComponents.js src/components/tabs/ src/views/audit/` and resolve fatal errors.
- [x] 8.3 Run `NODE_ENV=production npx webpack --config webpack.config.js` and confirm the production build succeeds. (Bundle size warnings are acceptable; only fatal errors block.)
- [x] 8.4 Stage all created/modified files in 3-4 logical commits; commit on `feature/zaakafhandelapp-manifest-v1` with no `Co-Authored-By` trailer.

## 9. Sign-off (per ADR-024 §9)

- [x] 9.1 `src/manifest.json` validates against the canonical schema.
- [x] 9.2 `manifest.dependencies` is `["openregister"]`.
- [x] 9.3 Tier choice is explicit (Tier 4 — full `CnAppRoot` + `CnAppNav` + `CnPageRenderer`).
- [x] 9.4 `manifest.version` is `"1.0.0"`.
- [x] 9.5 Custom-fallback inventory is documented and categorised.
- [ ] 9.6 Browser regression suite confirms all 28 routes resolve and render — **deferred** to follow-up; runs against a live Nextcloud instance once the worktree's npm-install + webpack build finishes cleanly.
