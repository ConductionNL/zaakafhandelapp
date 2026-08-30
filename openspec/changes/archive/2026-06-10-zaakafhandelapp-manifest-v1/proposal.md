# Zaakafhandelapp — manifest v1: migrate to JSON manifest renderer

## Why

`zaakafhandelapp` (Zaak Afhandel App — Dutch government case-handling) currently
ships **no `src/manifest.json` at all**. Routing is done via a single
catch-all vue-router config (`src/router/router.ts`) that maps every URL
segment to a component through a hand-maintained
`src/views/componentMapping.js` registry. The shell mounts a per-app
`MainMenu.vue`, a `Views.vue` host, a `SideBars.vue` slot, and modal
hosts (`Modals.vue`, `Dialogs.vue`) directly on `App.vue`.

The fleet-wide convention (ADR-024) says every Conduction app SHOULD
declare its routes, navigation, and page configuration in a single
`src/manifest.json` validated against `@conduction/nextcloud-vue`'s
canonical schema, and SHOULD adopt the highest tier of the renderer
that the lib release supports. With `@conduction/nextcloud-vue@1.0.0-beta.12`
released — the JSON manifest renderer plus the Vue.extend
frozen-component fix — zaakafhandelapp can move straight to Tier 4
(`CnAppRoot` shell + manifest-driven router) without staging through
Tiers 1–3.

The decidesk reference migration (PR #160, branch `development`,
seven commits, lands `src/manifest.json` + `CnAppRoot` adoption +
`customComponents.js` registry + per-page `customComponents/tabs/`) is
the canonical Tier-4 example. This change mirrors the decidesk
playbook for zaakafhandelapp.

Domain notes that shape the migration:

- The app is a case-handling workspace. Most pages are **schema-backed
  index/detail pairs** for the Dutch zaak-domain entities: zaken,
  zaaktypen, taken, statussen, resultaten, besluiten, rollen,
  documenten, berichten, contactmomenten, klanten, medewerkers.
- The current "split-pane" UI (list pane + details pane via
  `NcAppContent`) is implemented per-entity (e.g. `ZakenIndex.vue`
  hosts `ZakenList.vue` + `ZaakDetails.vue`). The manifest renderer's
  `index` + `detail` pages cover the same shape with one route each
  (`/zaken` and `/zaken/:id`); the embedded sidebar + detail tabs
  cover what the bespoke split panes did.
- A few pages are bespoke: `/zoeken` (a search-orchestration page),
  `/auditTrail` (a navigation entry with no current view binding —
  treated as a deferred follow-up), and the Pinia-backed Modals/
  Dialogs hosts (which stay outside the manifest as cross-cutting UI).
- This app uses **internal proxy controllers**, not OpenRegister
  registers + schemas — there is no `lib/Settings/<app>_register.json`.
  Index/detail pages rely on the lib's `useObjectStore` resolving
  through the existing pinia stores rather than the OR register
  abstraction. The migration therefore accepts a slightly higher
  share of `type: "custom"` entries than decidesk; resolving the
  internal-proxy tension is a follow-up tracked in
  `feedback_mydash-no-or-dependency.md`-style guidance.

## What Changes

- **Add `src/manifest.json`** declaratively describing the app:
  - 12 `type: "index"` entries — `Zaken`, `Taken`, `Klanten`,
    `Medewerkers`, `Contactmomenten`, `Berichten`, `Rollen`,
    `Zaaktypen`, `Besluiten`, `Documenten`, `Resultaten`, `Statussen`.
    Each declares `config.register` + `config.schema` + `config.columns`
    plus a sidebar block.
  - 12 sibling `type: "detail"` entries — `ZaakDetail` through
    `StatusDetail` — each binding to `route: "/<plural>/:id"` and
    declaring `config.sidebarTabs` for the per-entity tab inventory.
  - 1 `type: "dashboard"` entry — `Dashboard` (route `/`) — wrapping
    the existing `DashboardIndex.vue` widgets via the lib's
    dashboard widget registry. **Stub via `customComponents` if the
    KPI widgets fail validation against the lib's widget registry.**
  - 1 `type: "settings"` entry — `Settings` — backing the existing
    `Views/settings/Settings.vue` page through the lib's
    `manifest-settings-rich-sections` recipe (sections + custom
    components). **Stub via `customComponents` if the legacy
    settings page can't be re-shaped within this round.**
  - 2 `type: "custom"` entries — `Search` (the `/zoeken`
    orchestration view) and `AuditTrail` (placeholder navigation
    entry). Each preserves a `component:` reference into the existing
    Vue file and is tracked in the custom-fallback inventory.

- **Replace `src/main.js` + `src/App.vue` with `CnAppRoot`-driven
  bootstrap** mirroring decidesk's `b5c88cd2` + `4b49bca1` +
  `50e4df7c` commits:
  - `main.js` builds the vue-router config from the manifest
    (`routesFromManifest()`), shallow-clones `CnPageRenderer` to
    avoid Vue.extend's `_Ctor` cache mutation against
    non-extensible bundle exports, calls `tryLoadTranslations()`
    fire-and-forget, and mounts `<App>` unconditionally.
  - `App.vue` becomes a thin shell mounting `<CnAppRoot>` with the
    bundled manifest plus shallow copies of `defaultPageTypes` +
    `customComponents`. The `#sidebar` slot wires a single
    host-rendered `<CnObjectSidebar>` through the
    `objectSidebarState` provide/inject channel.
  - `MainMenu.vue`, `Views.vue`, `SideBars.vue` are kept on disk for
    one cycle (Modals/Dialogs hosts remain) but no longer mounted by
    `App.vue` — the manifest's `menu[]` drives `CnAppNav`. Deletion
    of those files is deferred to a follow-up cleanup commit so this
    change stays mechanically reviewable.

- **Add `src/customComponents.js`** exporting the `Search` and
  `AuditTrail` page components plus per-tab custom components that
  the detail-page `sidebarTabs` reference (e.g.
  `ZaakDocumentenTab`, `ZaakRollenTab`, `ZaakBesluitenTab`,
  `ZaakBerichtenTab`, `ZaakTakenTab`, `ZaakResultatenTab`,
  `ZaakStatussenTab`). Stubs are acceptable for this round —
  documented in `design.md`'s "Cleanup follow-up" section.

- **Add `src/router/index.js`** as a thin wrapper that re-exports
  the router built by `routesFromManifest()` so the existing
  `src/router/router.ts` can be retired in a follow-up. (For this
  round we replace `router.ts` with the manifest-driven `index.js`
  in-place to keep the cleanup small.)

- **Bump `@conduction/nextcloud-vue`** in `package.json` from
  whatever floor the development branch carries to
  `^1.0.0-beta.12`. (The lockfile resolves to `1.0.0-beta.13` which
  contains the same code plus a separate fix.)

- **Webpack alias for `@nextcloud/axios$`** mirroring decidesk's
  `ed34703c` commit — bypasses the package's `exports`-field gate
  that breaks `@nextcloud/vue`'s CJS `require('@nextcloud/axios')`.

- **Mirror `l10n/en_US.json` from `l10n/en.json`** — Nextcloud's
  l10n loader looks for the locale-with-region first; the lib uses
  `loadTranslations` which 404s without the `_US` copy.

- **Bump `appinfo/info.xml` `<version>`** from `0.1.31` to `0.2.0`
  to mark the manifest migration. This is a minor bump because the
  user-facing app behaviour does not change in this round; adoption
  of the renderer remains incremental.

- **Add `tests/validate-manifest.js`** mirroring the decidesk
  template — an Ajv-based Node script that validates
  `src/manifest.json` against
  `node_modules/@conduction/nextcloud-vue/src/schemas/app-manifest.schema.json`.
  Re-run with `node tests/validate-manifest.js`.

## Custom-fallback inventory

Pages that stay `type: "custom"` after this change:

| Page id | Reason | Category |
|---|---|---|
| `Search` | `/zoeken` orchestrates a multi-store query (cases, persons, organisations, contact moments) — the manifest's `index` + `detail` types don't model the cross-schema search shape. Migration cost is large and the lib has no `type: "search"` analogue. | Genuine exception |
| `AuditTrail` | The legacy `/auditTrail` route in `MainMenu.vue` has no view binding today. Kept as a `custom` page pointing at a placeholder component until the audit feature lands. | Migration cost (acceptable in v1) |
| `Settings` *(if `manifest-settings-rich-sections` shape doesn't fit)* | The current settings page combines admin-only fields with widget-based UI. If the section/widget shape doesn't cover everything, falls back to `custom`. Tracked as Open Question 1. | Lib gap (likely resolved → migrated) |
| `Dashboard` *(if dashboard widget registry doesn't cover the KPIs)* | `DashboardIndex.vue` mounts custom widget components. If the lib's widget registry doesn't expose equivalents, falls back to `custom`. Tracked as Open Question 2. | Lib gap (likely resolved → migrated) |

Final tally (best-case): **12 index + 12 detail + 1 dashboard + 1 settings + 2 custom = 28**.

## Capabilities

### New Capabilities

- `zaakafhandelapp-manifest-v1`: declarative manifest covering every
  zaakafhandelapp page (28 entries) and adoption of `CnAppRoot` as
  the app shell. Replaces the bespoke `componentMapping.js` registry
  + dynamic-route pattern with a manifest-driven dispatcher.

## Impact

- **New files**:
  - `zaakafhandelapp/src/manifest.json` — declarative shell.
  - `zaakafhandelapp/src/customComponents.js` — registry of survivors.
  - `zaakafhandelapp/src/router/index.js` — manifest-driven router (replaces `router.ts` for new code paths).
  - `zaakafhandelapp/src/components/tabs/<Per-Tab>.vue` — stub tab components for detail-page `sidebarTabs` that don't fit a built-in widget.
  - `zaakafhandelapp/tests/validate-manifest.js` — Ajv schema-validation script.
  - `zaakafhandelapp/openspec/changes/zaakafhandelapp-manifest-v1/{proposal,design,tasks}.md`
    and `specs/zaakafhandelapp-manifest-v1/spec.md`.

- **Modified files**:
  - `zaakafhandelapp/src/main.js` — `CnAppRoot` bootstrap (mount-survivable).
  - `zaakafhandelapp/src/App.vue` — `CnAppRoot` shell, `objectSidebarState` channel.
  - `zaakafhandelapp/package.json` — `@conduction/nextcloud-vue@^1.0.0-beta.12`.
  - `zaakafhandelapp/webpack.config.js` — `@nextcloud/axios$` alias.
  - `zaakafhandelapp/appinfo/info.xml` — `<version>` bump 0.1.31 → 0.2.0.
  - `zaakafhandelapp/l10n/en_US.json` (new — mirror of `l10n/en.json`).

- **NOT modified in this commit** (deferred to cleanup follow-up):
  - `src/views/componentMapping.js` — old registry; remove once router cuts over.
  - `src/router/router.ts` — replaced by `src/router/index.js`; legacy file deletable in follow-up.
  - `src/views/Views.vue` — host component no longer mounted by App.vue.
  - `src/navigation/MainMenu.vue` — replaced by manifest-driven `CnAppNav`.
  - `src/sidebars/SideBars.vue` — replaced by `CnObjectSidebar` via `objectSidebarState`.
  - The dozens of per-entity `*Index.vue`, `*List.vue`, `*Details.vue` files — left in place; manifest entries that resolve to built-in `index`/`detail` types simply stop importing them. Removal is the cleanup commit's job.

- **Validates against**:
  - `node_modules/@conduction/nextcloud-vue/src/schemas/app-manifest.schema.json` (v1.2.0 bundled with `1.0.0-beta.13`).

## Risks

- **Internal-proxy data layer.** Zaakafhandelapp's pinia stores
  (`zaakStore`, `taakStore`, etc.) talk to in-app PHP controllers,
  not OpenRegister registers + schemas. The lib's `CnIndexPage` /
  `CnDetailPage` expect `register`/`schema` bindings against
  `useObjectStore`, which in turn expects an OR-shaped backend.
  Mitigation: **declare** `register` + `schema` slugs that match
  the controller routes (e.g. `register: "zaakafhandelapp",
  schema: "zaak"`). At runtime the pages will issue
  `useObjectStore` reads against those slugs; if the lib's store
  can't reach them, the per-page legacy components stay available
  via the `customComponents` fallback. Track as a follow-up: either
  back the controllers with OR (preferred long-term) or extend the
  lib to plug consumer-supplied resolvers.
- **Sidebar tab implementations are stubbed.** Detail-page tabs
  (zaak documents, audit, related parties, tasks, etc.) reference
  custom components that may currently be empty placeholders.
  Validation passes regardless (schema is open-enum on tab
  component names); UI displays a `console.warn` for unresolved
  registry names. Implementation lands in a follow-up commit.
- **Dashboard widget mapping unknown.** The current
  `DashboardIndex.vue` mounts `ZakenWidget`, `TakenWidget`,
  `KlantenWidget`, etc., which are PHP-Dashboard-API widgets. The
  manifest's `dashboard` widget registry uses different semantics.
  Worst case: declare `Dashboard` as `type: "custom"` for v1 and
  bind it to the existing `DashboardIndex.vue`.

## Out of scope

- **OR-backed schema migration.** Switching the in-app
  controllers to OpenRegister-backed registers + schemas is a
  separate change (`zaakafhandelapp-or-adoption-v1`).
- **Dropping legacy per-page Vue files.** All per-entity
  `*Index.vue`, `*List.vue`, `*Details.vue` files stay in place
  for this commit and are deleted in a follow-up cleanup commit
  once runtime regression confirms the manifest dispatcher.
- **Backend `/api/manifest` endpoint.** The optional admin-overlay
  endpoint described in ADR-024 §4 is not implemented; the loader
  silently falls back when 404 returns.
- **Sidebar tab full implementations.** Each detail page's
  `sidebarTabs` array references custom components; this change
  ships stubs (or `<!-- TODO -->` placeholders) and defers full
  implementations to a sibling change.
- **Multi-tenancy / i18n / resolver consumer wiring.** Parked for
  separate per-app changes.

## See also

- `hydra/openspec/architecture/adr-024-app-manifest.md` — fleet-wide
  manifest convention.
- `decidesk/openspec/changes/decidesk-manifest-v1/` — the canonical
  Tier-4 reference migration (PR #160).
- `nextcloud-vue/src/schemas/app-manifest.schema.json` — canonical
  schema (v1.2.0 in `1.0.0-beta.13`).
- `feedback_design-system-cd-first.md` (project memory) — branching
  + commit hygiene rules for parallel work.
