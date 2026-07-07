---
status: proposed
---

# Navigation — shared menu pipeline adoption

## Purpose

Zaakafhandelapp SHALL build its runtime manifest through the ADR-037 fragment pipeline and the shared `buildManifest(base, fragments, menuLayout)` helper from `@conduction/nextcloud-vue`, with navigation layout declared in `src/menu-layout.json`. Configuration/admin leaves MUST be promoted into the settings foldout. No pre-existing page route or reachable menu function may be dropped.

**Governing decisions**: ADR-044 (Menu architecture), ADR-037 (Modular manifest fragment pipeline).

---

## ADDED Requirements

### Requirement: REQ-NAV-001 — zaakafhandelapp MUST introduce the ADR-037 modular fragment pipeline as a prerequisite for shared menu pipeline adoption (ADR-044)

Zaakafhandelapp SHALL replace its single monolithic `src/manifest.json` with a `src/manifest.d/` directory containing per-domain fragment files, loaded via `require.context` (or equivalent Webpack glob import) and merged at build time. The root `src/manifest.json` MUST be retained only as the base skeleton (version, dependencies, empty menu/pages arrays) that `buildManifest` accepts as its first argument.

#### Scenario: fragment directory exists and contains domain fragments

- GIVEN the zaakafhandelapp frontend build is complete
- WHEN the `src/manifest.d/` directory is inspected
- THEN it MUST contain at least the following fragment files: one for the `CasesGroup` menu subtree (`Zaken`, `Taken`, `Search` leaves), one for the `RelationsGroup` subtree (`Klanten`, `Medewerkers`, `Contactmomenten`, `Berichten` leaves), one for primary top-level entries (`Dashboard`), and one for configuration/admin entries (`Zaaktypen`, `Rollen`, `AuditTrail`, `SettingsMenu`)

#### Scenario: fragments are loaded via require.context in main.js

- GIVEN the `src/main.js` bootstrap file
- WHEN the file is read
- THEN it MUST import fragments via `require.context('./manifest.d', false, /\.json$/)` (or equivalent dynamic import) and MUST NOT import the full monolithic manifest directly as the sole manifest source

### Requirement: REQ-NAV-002 — zaakafhandelapp MUST compose its runtime manifest via the shared buildManifest helper (ADR-044), with navigation layout data in src/menu-layout.json

Zaakafhandelapp SHALL call `buildManifest(base, fragments, menuLayout)` from `@conduction/nextcloud-vue` to produce the manifest passed to `CnAppRoot`. The `menuLayout` argument MUST be read from `src/menu-layout.json`. No inline menu array may be passed directly to `CnAppRoot` as the final manifest.

#### Scenario: buildManifest is called at bootstrap

- GIVEN the zaakafhandelapp frontend bundle is loaded in a browser
- WHEN the `App.vue` or `main.js` source is inspected
- THEN it MUST import `buildManifest` from `@conduction/nextcloud-vue` and call it with three arguments: the base manifest skeleton, the collected fragments, and the menu-layout object read from `src/menu-layout.json`

#### Scenario: src/menu-layout.json exists and contains relocations and settingsSection keys

- GIVEN the zaakafhandelapp source tree
- WHEN `src/menu-layout.json` is read
- THEN it MUST be a valid JSON object containing at least the keys `relocations` (array, MAY be empty), `removals` (array, MAY be empty), and `settingsSection` (object or array listing the ids to promote into the settings foldout)

### Requirement: REQ-NAV-003 — zaakafhandelapp MUST lift its configuration/admin leaves into the settings foldout via settingsSection in src/menu-layout.json

The four menu leaves that currently carry `"section": "settings"` in the monolithic manifest — `Zaaktypen` (Case types, route: Zaaktypen), `Rollen` (Roles, route: Rollen), `AuditTrail` (Audit trail, route: AuditTrail), and `SettingsMenu` (Settings, route: Settings) — MUST be declared under `settingsSection` in `src/menu-layout.json`. They MUST NOT appear as promoted entries in the primary navigation column after the refactor.

#### Scenario: settings foldout contains all four admin leaves

- GIVEN the settings foldout is opened in a browser by an admin user
- WHEN the settings foldout navigation items are enumerated
- THEN the items MUST include `Zaaktypen`, `Rollen`, `AuditTrail`, and `SettingsMenu` and MUST render with their original labels ("Case types", "Roles", "Audit trail", "Settings") and icons

#### Scenario: primary navigation column does not contain admin leaves

- GIVEN the primary navigation column is visible in the app sidebar
- WHEN the navigation items are enumerated (excluding the settings foldout trigger)
- THEN none of `Zaaktypen`, `Rollen`, `AuditTrail`, or `SettingsMenu` MAY appear as top-level primary navigation entries

### Requirement: REQ-NAV-004 — the refactor MUST NOT drop any pre-existing menu entry or make any pre-existing page route unreachable

Every menu leaf id that existed in the monolithic manifest — `Dashboard`, `Zaken`, `Taken`, `Search`, `Klanten`, `Medewerkers`, `Contactmomenten`, `Berichten`, `Zaaktypen`, `Rollen`, `AuditTrail`, `SettingsMenu`, `Documentation`, `FeaturesRoadmapMenu` — MUST remain reachable in the post-refactor app. Every page route registered in the monolithic manifest — including the zaak detail deep-link `/zaken/:id`, the taak detail `/taken/:id`, the klant detail `/klanten/:id`, and all other `:id` parametric routes — MUST continue to resolve to the correct page component.

#### Scenario: deep-link to zaak detail route survives the refactor

- GIVEN the shared-menu-pipeline refactor is applied and the app is running
- WHEN a user navigates directly to `/apps/zaakafhandelapp/zaken/some-uuid`
- THEN the router MUST resolve the route to the `ZaakDetail` page (route id `ZaakDetail`, route pattern `/zaken/:id`) and the case detail view MUST render without a 404 or blank page

#### Scenario: all primary menu leaves remain navigable

- GIVEN the shared-menu-pipeline refactor is applied and the app is running
- WHEN each of the menu leaf ids `Dashboard`, `Zaken`, `Taken`, `Search`, `Klanten`, `Medewerkers`, `Contactmomenten`, and `Berichten` is activated (by navigation click or direct URL)
- THEN each MUST load its corresponding page without error and the active menu item MUST be highlighted correctly

#### Scenario: footer leaves remain reachable

- GIVEN the shared-menu-pipeline refactor is applied and the app is running
- WHEN the footer navigation area is inspected
- THEN `Documentation` (external link to https://zaakafhandelapp.conduction.nl) and `FeaturesRoadmapMenu` (route: FeaturesRoadmap) MUST both be present and functional
