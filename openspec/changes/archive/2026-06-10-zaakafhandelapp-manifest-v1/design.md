# Design — Zaakafhandelapp manifest v1: JSON manifest renderer adoption

## Approach

Zaakafhandelapp's current shell mixes:

- A single dynamic vue-router config (`src/router/router.ts`) that
  matches `/`, then `/:view/:id?` against a hand-maintained
  `componentMapping.js` registry of 13 entries.
- A bespoke `App.vue` mounting `MainMenu`, a `Views.vue` host,
  `SideBars`, `Modals`, `Dialogs`.
- ~60 per-entity Vue files under `src/views/` split into
  `*Index.vue` / `*List.vue` / `*Details.vue` triplets per resource
  (zaken, taken, klanten, …) and a flat-bag of dashboard widgets.

Three decidesk reference commits define the playbook we mirror:

- `b5c88cd2` — initial manifest rewrite + Tier-4 mount.
- `4b49bca1` — `CnAppRoot` adoption + cleanup of obsolete shell parts.
- `ed34703c` — lib bump + webpack alias for `@nextcloud/axios$`.
- `50e4df7c` — mount-survivable bootstrap (fire-and-forget translations).
- `866ff132` — `defaultPageTypes` / `customComponents` shallow-clone for
  Vue.extend's `_Ctor` cache against frozen module exports.

We mirror these into a single migration on `feature/zaakafhandelapp-manifest-v1`:

1. Author `src/manifest.json` describing every page (28 entries:
   12 indexes + 12 details + 1 dashboard + 1 settings + 2 customs).
2. Replace `src/main.js` with a `routesFromManifest()` bootstrap
   (shallow-clone `CnPageRenderer`, fire-and-forget translations,
   unconditional mount).
3. Replace `src/App.vue` with a `<CnAppRoot>` shell that passes the
   bundled manifest plus shallow copies of `defaultPageTypes` +
   `customComponents` and provides the `objectSidebarState` channel
   for `<CnObjectSidebar>` via `#sidebar`.
4. Add `src/customComponents.js` exporting the survivors plus per-tab
   custom components.
5. Add `src/router/index.js` — manifest-driven router. Replaces
   `src/router/router.ts` for new code paths; the legacy `.ts` file
   stays one cycle for cleanup.
6. Bump `@conduction/nextcloud-vue` to `^1.0.0-beta.12` in
   `package.json`.
7. Add `@nextcloud/axios$` alias in `webpack.config.js`.
8. Mirror `l10n/en_US.json` from `l10n/en.json`.
9. Add `tests/validate-manifest.js`.
10. Bump `appinfo/info.xml` `<version>` to `0.2.0`.

The change is intentionally Tier-4 in one shot: the lib is published
(`1.0.0-beta.12`), so unlike decidesk's 7-commit history we don't
need a forward-compatible-only round followed by an adopt round.

## Per-page mapping table

The 13 logical resource pages from `componentMapping.js` plus the
dashboard, settings, search, and audit entries map as follows. Each
non-custom entry binds `register: "zaakafhandelapp"` (logical) and a
schema slug matching the resource's pinia store. Columns are sourced
from each page's existing `*List.vue` displayed columns / fields.

| Manifest id | Route | Type | Config sketch | Reason |
|---|---|---|---|---|
| `Dashboard` | `/` | `dashboard` | `{ widgets: [zakenKpi, takenKpi, openZakenKpi, contactmomentenKpi, personenKpi, organisatiesKpi], layout: [6 grid items] }` | Existing dashboard mounts six `*Widget.vue` components; we declare them as `widgetDef` entries. Falls back to `custom` if the widget registry doesn't expose equivalents (Open Question 2). |
| `Zaken` | `/zaken` | `index` | `{ register: "zaakafhandelapp", schema: "zaak", columns: ["identificatie","omschrijving","zaaktype","status","uiterlijkeEinddatumAfdoening"], sidebar: { enabled: true } }` | Replaces `ZakenIndex.vue` + `ZakenList.vue` split-pane. |
| `ZaakDetail` | `/zaken/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "zaak", sidebarTabs: [overview, taken, rollen, documenten, besluiten, berichten, resultaten, statussen, audit] }` | Replaces `ZaakDetails.vue` plus the per-tab modal browsers. |
| `Taken` | `/taken` | `index` | `{ register: "zaakafhandelapp", schema: "taak", columns: ["title","status","priority","dueDate"], sidebar: { enabled: true } }` | Replaces `TakenIndex.vue`. |
| `TaakDetail` | `/taken/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "taak", sidebarTabs: [overview, audit] }` | Replaces `TaakDetails.vue`. |
| `Klanten` | `/klanten` | `index` | `{ register: "zaakafhandelapp", schema: "klant", columns: ["naam","email","telefoon","type"], sidebar: { enabled: true } }` | Replaces `KlantenIndex.vue`. |
| `KlantDetail` | `/klanten/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "klant", sidebarTabs: [overview, audit] }` | Replaces `KlantDetails.vue`. |
| `Medewerkers` | `/medewerkers` | `index` | `{ register: "zaakafhandelapp", schema: "medewerker", columns: ["naam","email","afdeling"], sidebar: { enabled: true } }` | Replaces `MedewerkerIndex.vue`. |
| `MedewerkerDetail` | `/medewerkers/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "medewerker", sidebarTabs: [overview, audit] }` | Replaces `MedewerkerDetails.vue`. |
| `Contactmomenten` | `/contactmomenten` | `index` | `{ register: "zaakafhandelapp", schema: "contactmoment", columns: ["onderwerp","kanaal","datum"], sidebar: { enabled: true } }` | Replaces `ContactMomentenIndex.vue`. |
| `ContactmomentDetail` | `/contactmomenten/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "contactmoment", sidebarTabs: [overview, audit] }` | Replaces `ContactMomentDetails.vue`. |
| `Berichten` | `/berichten` | `index` | `{ register: "zaakafhandelapp", schema: "bericht", columns: ["onderwerp","kanaal","datum"], sidebar: { enabled: true } }` | Replaces `BerichtenIndex.vue`. |
| `BerichtDetail` | `/berichten/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "bericht", sidebarTabs: [overview, audit] }` | Replaces `BerichtDetails.vue`. |
| `Rollen` | `/rollen` | `index` | `{ register: "zaakafhandelapp", schema: "rol", columns: ["naam","type","subjectIdentificatie"], sidebar: { enabled: true } }` | Replaces `RollenIndex.vue`. |
| `RolDetail` | `/rollen/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "rol", sidebarTabs: [overview, audit] }` | Replaces `RolDetails.vue`. |
| `Zaaktypen` | `/zaaktypen` | `index` | `{ register: "zaakafhandelapp", schema: "zaaktype", columns: ["identificatie","omschrijving","versie"], sidebar: { enabled: true } }` | Settings-section index — replaces `ZakenTypenIndex.vue`. |
| `ZaaktypeDetail` | `/zaaktypen/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "zaaktype", sidebarTabs: [overview, audit] }` | Replaces `ZaakTypeDetails.vue`. |
| `Besluiten` | `/besluiten` | `index` | `{ register: "zaakafhandelapp", schema: "besluit", columns: ["identificatie","datum","status"], sidebar: { enabled: true } }` | Replaces `BesluitenIndex.vue`. |
| `BesluitDetail` | `/besluiten/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "besluit", sidebarTabs: [overview, audit] }` | Replaces `BesluitDetails.vue`. |
| `Documenten` | `/documenten` | `index` | `{ register: "zaakafhandelapp", schema: "document", columns: ["titel","type","datum"], sidebar: { enabled: true } }` | Replaces `DocumentenIndex.vue`. |
| `DocumentDetail` | `/documenten/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "document", sidebarTabs: [overview, audit] }` | Replaces `DocumentDetails.vue`. |
| `Resultaten` | `/resultaten` | `index` | `{ register: "zaakafhandelapp", schema: "resultaat", columns: ["omschrijving","datum"], sidebar: { enabled: true } }` | Replaces `ResultatenIndex.vue`. |
| `ResultaatDetail` | `/resultaten/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "resultaat", sidebarTabs: [overview, audit] }` | Replaces `ResultaatDetails.vue`. |
| `Statussen` | `/statussen` | `index` | `{ register: "zaakafhandelapp", schema: "status", columns: ["statustype","datum","statustoelichting"], sidebar: { enabled: true } }` | Replaces `StatussenIndex.vue`. |
| `StatusDetail` | `/statussen/:id` | `detail` | `{ register: "zaakafhandelapp", schema: "status", sidebarTabs: [overview, audit] }` | Replaces `StatusDetails.vue`. |
| `Search` | `/zoeken` | `custom` | `component: "SearchView"` | **Genuine exception** — multi-store search; no abstract analogue. |
| `AuditTrail` | `/auditTrail` | `custom` | `component: "AuditTrailView"` | Placeholder — no view binding currently; tracked as deferred. |
| `Settings` | `/settings` | `settings` | `{ saveEndpoint: "/index.php/apps/zaakafhandelapp/api/settings", sections: [{ component: "SettingsForm" }] }` | Wraps existing `Views/settings/Settings.vue` via `component: <name>` section. Falls back to `custom` if the section/widget shape doesn't fit (Open Question 1). |

Final tally: **12 index + 12 detail + 1 dashboard + 1 settings + 2 custom = 28**.

## Sidebar tab inventory

For `type: "detail"` pages, `config.sidebarTabs` declares an
open-enum array of tabs. `ZaakDetail` is the rich case (8 tabs);
every other detail page ships a minimal `[overview, audit]` set.

Tab shapes follow the lib's contract: `{ id, label, icon?, widgets?, component?, order? }`.

| Detail page | Tabs (with custom-component bindings) |
|---|---|
| `ZaakDetail` | `overview` (data + metadata widgets), `taken` (custom: `ZaakTakenTab`), `rollen` (custom: `ZaakRollenTab`), `documenten` (custom: `ZaakDocumentenTab`), `besluiten` (custom: `ZaakBesluitenTab`), `berichten` (custom: `ZaakBerichtenTab`), `resultaten` (custom: `ZaakResultatenTab`), `statussen` (custom: `ZaakStatussenTab`), `audit` (built-in audit-trail) |
| `TaakDetail` | `overview`, `audit` |
| `KlantDetail` | `overview`, `audit` |
| `MedewerkerDetail` | `overview`, `audit` |
| `ContactmomentDetail` | `overview`, `audit` |
| `BerichtDetail` | `overview`, `audit` |
| `RolDetail` | `overview`, `audit` |
| `ZaaktypeDetail` | `overview`, `audit` |
| `BesluitDetail` | `overview`, `audit` |
| `DocumentDetail` | `overview`, `audit` |
| `ResultaatDetail` | `overview`, `audit` |
| `StatusDetail` | `overview`, `audit` |

`ZaakDetail`'s seven custom tabs are **stubs** in this round — each
component renders a `CnNoteCard` placeholder. Full implementations
land in a follow-up sibling change. The manifest schema accepts
unknown component names and the renderer logs a console warning
rather than crashing, so this is mechanically safe.

## Dashboard widget inventory

`Dashboard` config sketch (best-case if widget types resolve):

```json
{
  "widgets": [
    { "id": "zaken-kpi", "type": "stats-block", "title": "Open zaken", "props": { "register": "zaakafhandelapp", "schema": "zaak" } },
    { "id": "taken-kpi", "type": "stats-block", "title": "Open taken", "props": { "register": "zaakafhandelapp", "schema": "taak" } },
    { "id": "open-zaken-kpi", "type": "stats-block", "title": "Lopende zaken", "props": { "register": "zaakafhandelapp", "schema": "zaak" } },
    { "id": "contactmomenten-kpi", "type": "stats-block", "title": "Contactmomenten", "props": { "register": "zaakafhandelapp", "schema": "contactmoment" } },
    { "id": "personen-kpi", "type": "stats-block", "title": "Personen", "props": { "register": "zaakafhandelapp", "schema": "klant" } },
    { "id": "organisaties-kpi", "type": "stats-block", "title": "Organisaties", "props": { "register": "zaakafhandelapp", "schema": "klant" } }
  ],
  "layout": [
    { "id": "zaken-kpi",            "widgetId": "zaken-kpi",            "gridX": 0, "gridY": 0, "gridWidth": 4, "gridHeight": 2 },
    { "id": "taken-kpi",            "widgetId": "taken-kpi",            "gridX": 4, "gridY": 0, "gridWidth": 4, "gridHeight": 2 },
    { "id": "open-zaken-kpi",       "widgetId": "open-zaken-kpi",       "gridX": 8, "gridY": 0, "gridWidth": 4, "gridHeight": 2 },
    { "id": "contactmomenten-kpi",  "widgetId": "contactmomenten-kpi",  "gridX": 0, "gridY": 2, "gridWidth": 4, "gridHeight": 2 },
    { "id": "personen-kpi",         "widgetId": "personen-kpi",         "gridX": 4, "gridY": 2, "gridWidth": 4, "gridHeight": 2 },
    { "id": "organisaties-kpi",     "widgetId": "organisaties-kpi",     "gridX": 8, "gridY": 2, "gridWidth": 4, "gridHeight": 2 }
  ]
}
```

## Custom-fallback inventory

### Genuine exceptions (lib-fit issue, not migration cost)

- **`Search`** — `/zoeken` orchestrates a multi-store search (cases,
  persons, organisations, contact moments, full-text) — the
  manifest's `index` + `detail` types don't model the cross-schema
  search shape.

### Lib gaps (could migrate if the lib were richer)

- **`Dashboard`** *(if widget registry doesn't expose `stats-block`)* — KPIs as Nextcloud Dashboard API widgets (`ZakenWidget` etc.) map naturally to a `stats-block` `widgetDef`, but the lib's current widget registry coverage may not include a count-with-filter widget. If validation rejects the `dashboard` form, downgrade `Dashboard` to `type: "custom"` mounting `DashboardIndex.vue`. Optimistically declared as `"dashboard"` in this round.
- **`Settings`** *(if section/widget shape doesn't fit)* — The current settings page combines custom UI with admin-only fields. The `manifest-settings-rich-sections` recipe (single section with `component: "SettingsForm"`) wraps the existing page; if the wrapping doesn't render correctly, downgrade to `type: "custom"`.

### Migration cost (acceptable to defer)

- **`AuditTrail`** — placeholder navigation entry today; no view binding. Stays `custom` until a real audit feature lands.

## Files affected

New:
- `src/manifest.json`
- `src/customComponents.js`
- `src/router/index.js` — manifest-driven router (replaces `router.ts`).
- `src/components/tabs/ZaakTakenTab.vue` (stub)
- `src/components/tabs/ZaakRollenTab.vue` (stub)
- `src/components/tabs/ZaakDocumentenTab.vue` (stub)
- `src/components/tabs/ZaakBesluitenTab.vue` (stub)
- `src/components/tabs/ZaakBerichtenTab.vue` (stub)
- `src/components/tabs/ZaakResultatenTab.vue` (stub)
- `src/components/tabs/ZaakStatussenTab.vue` (stub)
- `src/views/audit/AuditTrailView.vue` (placeholder)
- `tests/validate-manifest.js`
- `l10n/en_US.json` (mirror of `l10n/en.json`)

Modified:
- `src/main.js` — `CnAppRoot` bootstrap, `routesFromManifest()`.
- `src/App.vue` — `<CnAppRoot>` shell, `objectSidebarState` provide.
- `package.json` — `@conduction/nextcloud-vue@^1.0.0-beta.12`.
- `webpack.config.js` — `@nextcloud/axios$` alias.
- `appinfo/info.xml` — `<version>` 0.1.31 → 0.2.0.

Untouched (deferred):
- `src/router/router.ts` — legacy router file; deletable in cleanup.
- `src/views/Views.vue`, `src/navigation/MainMenu.vue`, `src/sidebars/SideBars.vue`,
  `src/views/componentMapping.js` — replaced functionally by `CnAppRoot` + manifest, files stay one cycle.
- `src/modals/Modals.vue` and `src/dialogs/Dialogs.vue` — cross-cutting
  modal hosts; remain mounted from `App.vue` alongside `<CnAppRoot>`
  (they hook into `navigationStore`, not the manifest).
- The 60+ per-entity `*Index.vue`, `*List.vue`, `*Details.vue` files — no longer wired into routes by the manifest dispatcher; deletion is the cleanup commit's job.

## Cleanup follow-up

Items deferred to a separate cleanup commit:

1. Delete `src/router/router.ts`, `src/views/componentMapping.js`,
   `src/views/Views.vue`, `src/navigation/MainMenu.vue`,
   `src/sidebars/SideBars.vue`.
2. Decide per-tab implementations vs. modal-store reuse for the
   seven `Zaak*Tab.vue` stubs. Most should reuse the existing
   modals (`ViewZaakAuditTrail`, `AddTaakToZaak`, `AddRolToZaak`,
   `AddBerichtToZaak`) — wire the tabs as data tables that
   open the existing modals.
3. Decide whether to back the controllers with OpenRegister registers
   + schemas (preferred long-term) or extend the lib's `useObjectStore`
   to plug consumer-supplied resolvers.
4. Delete the per-entity `*Index.vue`, `*List.vue`, `*Details.vue`
   files. Quick path: any view component still referenced from
   `customComponents.js` stays; everything else goes.
5. Run the full Playwright regression suite (per the existing
   regression-tests change) and confirm every route still renders.

## Open questions

1. **Settings shape.** The current `Views/settings/Settings.vue`
   combines admin-only PHP settings (server endpoint, OAuth client
   IDs) with widget-based UI. The `manifest-settings-rich-sections`
   recipe (per-section `component:` with optional `widgets:`) should
   cover this — wrap the legacy file as a single section component.
   If that doesn't render correctly at runtime, downgrade to
   `type: "custom"` for v1. Default: declare as `"settings"`
   optimistically.
2. **Dashboard widgets.** The lib's `dashboard` widget registry may
   not expose count-with-filter "stats" widgets. Default: declare
   `Dashboard` as `type: "dashboard"` optimistically and downgrade
   to `"custom"` if validation/runtime rejects.
3. **Internal-proxy data layer.** The pinia stores
   (`zaakStore`, `taakStore`, etc.) talk to in-app PHP controllers,
   not OR. The lib's `useObjectStore` expects OR-shaped routes.
   Worst case: the index pages render empty / error; the legacy
   Vue files remain available for re-binding via `customComponents`.
   Tracked as a follow-up rather than blocking this migration.

## Citations

- **Library schema**:
  `@conduction/nextcloud-vue@1.0.0-beta.13/src/schemas/app-manifest.schema.json`
  (v1.2.0).
- **Library renderer + components**:
  `nextcloud-vue` package — `CnAppRoot`, `CnAppNav`, `CnPageRenderer`,
  `defaultPageTypes`, `CnObjectSidebar`.
- **Cross-app convention**:
  `hydra/openspec/architecture/adr-024-app-manifest.md`.
- **Reference migration**: `decidesk` PR #160 (commits `b5c88cd2`,
  `4b49bca1`, `ed34703c`, `50e4df7c`, `866ff132`).
- **Project memory**: `feedback_design-system-cd-first.md`,
  `feedback_long-term-app-feature-decisions.md`,
  `feedback_opsx-no-process-tasks.md`.
