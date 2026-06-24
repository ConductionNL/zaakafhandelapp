# Tasks: adopt-shared-menu-pipeline

## 1. Introduce ADR-037 fragment pipeline (REQ-NAV-001)

- [ ] 1.1 Create `src/manifest.d/` directory with the following fragment files, each containing the relevant slice of the current monolithic manifest:
  - `primary.json` — `Dashboard` top-level leaf
  - `cases.json` — `CasesGroup` group with children `Zaken`, `Taken`, `Search` plus their corresponding `pages` entries (`Zaken`, `ZaakDetail`, `Taken`, `TaakDetail`, `Search`)
  - `relations.json` — `RelationsGroup` group with children `Klanten`, `Medewerkers`, `Contactmomenten`, `Berichten` plus their corresponding `pages` entries (`Klanten`, `KlantDetail`, `Medewerkers`, `MedewerkerDetail`, `Contactmomenten`, `ContactmomentDetail`, `Berichten`, `BerichtDetail`)
  - `admin.json` — settings-section leaves `Zaaktypen`, `Rollen`, `AuditTrail`, `SettingsMenu` plus their corresponding `pages` entries (`Zaaktypen`, `ZaaktypeDetail`, `Rollen`, `RolDetail`, `AuditTrail`, `Settings`)
  - `footer.json` — footer leaves `Documentation`, `FeaturesRoadmapMenu` plus the `FeaturesRoadmap` page entry
  - `zgw-detail.json` — ZGW-related index/detail page entries not surfaced in the primary menu: `Besluiten`, `BesluitDetail`, `Documenten`, `DocumentDetail`, `Resultaten`, `ResultaatDetail`, `Statussen`, `StatusDetail`
- [ ] 1.2 Reduce `src/manifest.json` to a base skeleton: retain `$schema`, `version`, `dependencies`; empty `menu` and `pages` arrays (the fragment pipeline provides all items)
- [ ] 1.3 Wire `require.context('./manifest.d', false, /\.json$/)` (or equivalent Webpack `require.context` call) in `src/main.js` to collect all fragments into an array passed to `buildManifest`

## 2. Add buildManifest call and menu-layout.json (REQ-NAV-002)

- [ ] 2.1 Import `buildManifest` from `@conduction/nextcloud-vue` in `src/main.js` (or `src/App.vue` if manifest composition lives there)
- [ ] 2.2 Create `src/menu-layout.json` with the following initial content:
  ```json
  {
    "relocations": [],
    "removals": [],
    "settingsSection": {
      "gear": ["Zaaktypen", "Rollen", "AuditTrail", "SettingsMenu"]
    }
  }
  ```
- [ ] 2.3 Replace the direct `bundledManifest` import passed to `CnAppRoot` with `buildManifest(baseManifest, fragments, menuLayout)` — the result is the manifest prop; the base skeleton, collected fragments, and `menu-layout.json` content are the three arguments

## 3. Lift admin leaves into settings foldout (REQ-NAV-003)

- [ ] 3.1 Confirm that `settingsSection.gear` in `src/menu-layout.json` lists `Zaaktypen`, `Rollen`, `AuditTrail`, and `SettingsMenu` (done in Task 2.2)
- [ ] 3.2 Remove the `"section": "settings"` inline annotations from the `admin.json` fragment (the foldout promotion is now data-driven via `menu-layout.json`, not inline manifest properties)
- [ ] 3.3 Manually verify in a running dev instance: open the settings foldout and confirm all four entries appear; confirm the primary nav column does not show them

## 4. Verify the no-drop invariant (REQ-NAV-004)

- [ ] 4.1 Enumerate all 14 pre-existing menu leaf ids and all page route patterns from the original monolithic manifest; cross-check against the post-refactor fragment set to confirm none is missing
- [ ] 4.2 Run the existing zaakafhandelapp e2e suite: confirm deep-link navigation to `/apps/zaakafhandelapp/zaken/<uuid>` resolves `ZaakDetail` and the case detail view renders
- [ ] 4.3 Confirm all primary-nav leaves (`Dashboard`, `Zaken`, `Taken`, `Search`, `Klanten`, `Medewerkers`, `Contactmomenten`, `Berichten`) load their pages without error
- [ ] 4.4 Confirm footer leaves `Documentation` and `FeaturesRoadmapMenu` remain present and functional

## 5. Quality gates and delivery

- [ ] 5.1 `composer check:strict` green (PHP side unaffected; confirm no PHP changes required)
- [ ] 5.2 All 18 Hydra gates green; fix any pre-existing gate issues encountered in touched files in the same batch
- [ ] 5.3 Gate-22 manifest validation green against the post-`buildManifest` effective manifest
- [ ] 5.4 Deliver as a Codeberg PR against `development` (zaakafhandelapp is on the racing-PR list — NEVER direct push to development; orchestration force-resets the branch)
