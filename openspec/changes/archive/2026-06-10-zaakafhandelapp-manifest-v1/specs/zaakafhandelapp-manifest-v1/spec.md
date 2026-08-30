---
status: draft
---
# Zaakafhandelapp manifest v1 — JSON manifest renderer adoption

## Purpose

Author `zaakafhandelapp/src/manifest.json` and adopt
`@conduction/nextcloud-vue@^1.0.0-beta.12`'s JSON manifest renderer
(`CnAppRoot` + `CnAppNav` + `CnPageRenderer`) as the app shell. Mirrors
the decidesk Tier-4 reference (PR #160) per ADR-024.

This spec captures the additive requirements that bring zaakafhandelapp
under the fleet-wide manifest convention. There is no pre-existing
manifest to modify; this is a NEW capability.

## ADDED Requirements

### Requirement: REQ-ZMV1-1 The app SHIPS a `src/manifest.json` validating against the canonical schema

`zaakafhandelapp/src/manifest.json` MUST exist, declare
`$schema` pointing at the published canonical
`https://raw.githubusercontent.com/ConductionNL/nextcloud-vue/main/src/schemas/app-manifest.schema.json`,
declare `version: "1.0.0"`, and validate without errors against the
schema bundled with `@conduction/nextcloud-vue@^1.0.0-beta.12`.

#### Scenario: Validator script exits 0
- GIVEN the bundled `src/manifest.json`
- AND the schema bundle from `node_modules/@conduction/nextcloud-vue/src/schemas/app-manifest.schema.json`
- WHEN running `node tests/validate-manifest.js`
- THEN the script MUST exit with status code 0
- AND it MUST print a success line confirming zero validation errors

#### Scenario: Manifest declares dependencies = openregister
- GIVEN `src/manifest.json`
- WHEN reading `manifest.dependencies`
- THEN it MUST equal `["openregister"]`

### Requirement: REQ-ZMV1-2 Index pages MUST be `type: "index"` with declarative config

The 12 list/index pages — `Zaken`, `Taken`, `Klanten`,
`Medewerkers`, `Contactmomenten`, `Berichten`, `Rollen`, `Zaaktypen`,
`Besluiten`, `Documenten`, `Resultaten`, `Statussen` — MUST declare
`type: "index"` in `src/manifest.json`. Each entry MUST declare
`config.register: "zaakafhandelapp"`, `config.schema: <slug>`, and
`config.columns: string[]` listing the columns the page renders. The
sidebar block (`config.sidebar.enabled: true`) is RECOMMENDED for
schema-driven facet filtering.

#### Scenario: Zaken index validates
- GIVEN `src/manifest.json` page entry for `Zaken` with `type: "index"`, `config: { register: "zaakafhandelapp", schema: "zaak", columns: [...], sidebar: { enabled: true } }`
- WHEN `validateManifest()` runs against the v1.2.0 schema
- THEN it MUST return `{ valid: true, errors: [] }`

#### Scenario: Every index entry binds register + schema
- GIVEN any of the 12 index pages
- WHEN inspecting its `config`
- THEN the entry MUST set `config.register === "zaakafhandelapp"`
- AND it MUST set `config.schema` to a non-empty string

### Requirement: REQ-ZMV1-3 Detail pages MUST be `type: "detail"` with `sidebarTabs`

The 12 detail pages — `ZaakDetail`, `TaakDetail`, `KlantDetail`,
`MedewerkerDetail`, `ContactmomentDetail`, `BerichtDetail`,
`RolDetail`, `ZaaktypeDetail`, `BesluitDetail`, `DocumentDetail`,
`ResultaatDetail`, `StatusDetail` — MUST declare `type: "detail"`.
Each entry MUST declare `config.register: "zaakafhandelapp"` and
`config.schema: <slug>`. Each entry SHOULD declare
`config.sidebarTabs: SidebarTab[]` with at minimum
`[{ id: "overview", widgets: [{ type: "data" }, { type: "metadata" }] }, { id: "audit", widgets: [{ type: "audit-trail" }] }]`.

#### Scenario: ZaakDetail dispatches via detail with sidebarTabs
- GIVEN `pages[]` contains `{ id: "ZaakDetail", route: "/zaken/:id", type: "detail", config: { register: "zaakafhandelapp", schema: "zaak", sidebarTabs: [...] } }`
- WHEN `validateManifest()` runs
- THEN it MUST return `{ valid: true, errors: [] }`

#### Scenario: ZaakDetail tabs include the rich seven-tab inventory
- GIVEN the `ZaakDetail` page entry
- WHEN inspecting `config.sidebarTabs[*].id`
- THEN the array MUST contain (in any order): `overview`, `taken`, `rollen`, `documenten`, `besluiten`, `berichten`, `resultaten`, `statussen`, `audit`

### Requirement: REQ-ZMV1-4 Dashboard MUST be `type: "dashboard"` with widgets + layout

The `Dashboard` page MUST declare `type: "dashboard"` (route `/`)
with `config.widgets: WidgetDef[]` and `config.layout: LayoutItem[]`.
Six widget entries — zaken-kpi, taken-kpi, open-zaken-kpi,
contactmomenten-kpi, personen-kpi, organisaties-kpi — MUST each
appear in `config.widgets` referencing a schema slug from the
zaakafhandelapp register and a stable id.

#### Scenario: Dashboard manifest entry validates
- GIVEN `pages[]` contains `{ id: "Dashboard", route: "/", type: "dashboard", config: { widgets: [...6 entries...], layout: [...6 entries...] } }`
- WHEN `validateManifest()` runs
- THEN it MUST return `{ valid: true, errors: [] }`

### Requirement: REQ-ZMV1-5 Settings MUST be `type: "settings"` wrapping the legacy view

The `Settings` page MUST declare `type: "settings"` with
`config.saveEndpoint: "/index.php/apps/zaakafhandelapp/api/settings"`
and `config.sections: [{ component: "SettingsForm" }]` so the
existing `views/settings/Settings.vue` page renders as a single
section component via the `manifest-settings-rich-sections` recipe.

If the section/widget shape doesn't render correctly at runtime,
this requirement MAY be downgraded to `type: "custom"` in a
follow-up commit (tracked as Open Question 1).

#### Scenario: Settings entry references SettingsForm
- GIVEN the `Settings` page entry
- WHEN inspecting `config.sections[0].component`
- THEN it MUST equal `"SettingsForm"`
- AND `customComponents.js` MUST export a `SettingsForm` entry resolving to `views/settings/Settings.vue`

### Requirement: REQ-ZMV1-6 Custom-fallback inventory MUST stay at exactly 2 entries

After this migration, exactly two pages MUST be `type: "custom"`:
`Search` (genuine multi-store exception) and `AuditTrail` (placeholder
navigation entry). Each surviving custom entry MUST keep its
`component:` field intact (referencing `SearchView` and
`AuditTrailView` respectively). The Dashboard and Settings pages
MAY downgrade to `custom` in a follow-up commit only if runtime
validation forces the fallback.

#### Scenario: Exactly two custom pages declared up-front
- GIVEN `src/manifest.json`
- WHEN counting `pages[*].type === "custom"`
- THEN the count MUST be exactly 2
- AND the two ids MUST be `Search` and `AuditTrail`

#### Scenario: Each surviving custom points at a registered component
- GIVEN any `pages[*].type === "custom"` entry
- WHEN inspecting its `component` field
- THEN the value MUST be a non-empty string
- AND that string MUST be a key in `customComponents.js`'s default export

### Requirement: REQ-ZMV1-7 The app shell MUST mount `<CnAppRoot>` at Tier 4

`src/App.vue` MUST mount `<CnAppRoot :manifest :customComponents :pageTypes :appId :translate :permissions>`
with the `#sidebar` slot wired to a single host-rendered `<CnObjectSidebar>` driven by a reactive `objectSidebarState` data object provided via `provide()`. `src/main.js` MUST build the vue-router config from `manifest.pages` via a `routesFromManifest()` helper that maps each page to a route with `name === page.id`, component = a shallow-cloned `CnPageRenderer` reference, and `props: true` when the route declares a `:` parameter.

#### Scenario: Main.js shallow-clones CnPageRenderer + registry maps
- GIVEN `src/main.js`
- WHEN reading the file
- THEN there MUST be `const RoutePageRenderer = { ...CnPageRenderer }` (or equivalent shallow-clone) before vue-router's `new Router({ routes: ... })` call
- AND it MUST shallow-clone `defaultPageTypes` (e.g. `{ ...defaultPageTypes }`) before passing to `<App>` as a prop
- AND it MUST shallow-clone the `customComponents` registry similarly

#### Scenario: App.vue mounts CnAppRoot with #sidebar slot
- GIVEN `src/App.vue`
- WHEN reading the SFC `<template>`
- THEN it MUST mount a `<CnAppRoot>` element with the `manifest`, `customComponents`, `pageTypes`, `appId`, `translate`, `permissions` props
- AND the `<CnAppRoot>` MUST contain a `#sidebar` slot mounting `<CnObjectSidebar>` bound to a reactive `objectSidebarState`
- AND `provide()` MUST expose `objectSidebarState` to descendants

### Requirement: REQ-ZMV1-8 Translations load fire-and-forget; mount MUST NOT depend on it

`src/main.js` MUST call `loadTranslations` in a try/catch wrapper that swallows rejections (or `.then(() => {}, () => {})` on the returned promise) BEFORE the `new Vue(...).$mount('#content')` line. The mount MUST run unconditionally — translation 404s in dev installs (where Apache rewrites unknown paths to index.php) MUST NOT stop the app from rendering. Strings fall back to their English source on miss.

#### Scenario: Mount call is unconditional and outside the translation-load promise
- GIVEN `src/main.js`
- WHEN reading the file
- THEN the `new Vue(...).$mount('#content')` call MUST be at top-level (not inside a `loadTranslations(...).then(() => { ... })` callback)

### Requirement: REQ-ZMV1-9 Webpack alias for `@nextcloud/axios$` MUST exist

`webpack.config.js` MUST alias the bare `@nextcloud/axios$` specifier
directly to the package's `dist/index.js` to bypass the package's
`exports`-field gate that breaks `@nextcloud/vue`'s CJS
`require('@nextcloud/axios')`.

#### Scenario: webpack.config.js declares the @nextcloud/axios$ alias
- GIVEN `webpack.config.js`
- WHEN reading the resolve.alias map
- THEN there MUST be a key `'@nextcloud/axios$'` resolving to `node_modules/@nextcloud/axios/dist/index.cjs` (the package's CJS dist entry — `dist/index.js` does not exist in `@nextcloud/axios@2.5.x`)

### Requirement: REQ-ZMV1-10 `l10n/en_US.json` MUST mirror `l10n/en.json`

`l10n/en_US.json` MUST exist and contain the same translations as
`l10n/en.json`. The lib's `loadTranslations` looks for the
locale-with-region (`en_US`) first; without the file, the lib
silently falls back to English.

#### Scenario: en_US.json exists and matches en.json
- GIVEN `l10n/en.json` and `l10n/en_US.json`
- WHEN comparing their JSON bodies
- THEN they MUST be byte-equal (or semantically equal — same `translations` map)

### Requirement: REQ-ZMV1-11 `appinfo/info.xml` `<version>` MUST bump to 0.2.0

`appinfo/info.xml`'s `<version>` element MUST equal `0.2.0` to mark
the manifest migration. Following bumps follow normal semver as the
manifest stabilises.

#### Scenario: info.xml version is 0.2.0
- GIVEN `appinfo/info.xml`
- WHEN reading `<version>`
- THEN it MUST equal `0.2.0`
