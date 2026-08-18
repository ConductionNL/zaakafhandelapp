# Tasks: Zaakafhandelapp Leaf Integrations

## 1. Schema-side contribution (owning register, OpenRegister side)

- [ ] 1.1 Contribute `linkedTypes` to the owning register's schema definitions (the register the `{type}_register` appconfig keys point at): `zaak` += `email`, `calendar`, `forms`; `taak` += `calendar`; `klant` += `contacts`; `contactmoment` += `email` — validated against `openregister/lib/Db/Schema.php:2494-2502` (array of strings)
- [ ] 1.2 Contribute `configuration.mailObjectTemplate` on `contactmoment`: `notitie` ← body, `kanaal` ← `"email"`, `startDate` ← received date, `titel` ← subject — validated against `openregister/lib/Db/Schema.php:2519-2547` (property-name → scalar)
- [ ] 1.3 Record the contribution reference (OpenRegister-side PR/config change) in this change's PR description; confirm zaakafhandelapp ships no `lib/Settings/*_register.json` (REQ-001 repository-shape assertion)

## 2. Manifest leaf widgets (this repo, `src/manifest.json` only)

- [ ] 2.1 Zaak detail page: add `{"type": "integration", "integrationId": "email"}`, `{"type": "integration", "integrationId": "calendar"}`, and `{"type": "integration", "integrationId": "forms"}` widgets plus layout rows, alongside the existing `zaak-files` widget (line 412)
- [ ] 2.2 Taak detail page: add a `calendar` integration widget plus layout row, alongside `taak-files` (line 559)
- [ ] 2.3 Klant detail page: add a `contacts` integration widget plus layout row; verify it renders the linkage `KlantContactSyncService` maintains (no second link store — REQ-004)
- [ ] 2.4 Rewrite the affected archetype `_note`s (zaak 398, taak 555, klant 605, contactmoment 727) to name the leaves now declared; leave medewerker (666), bericht (782), rol (829) `_note`s untouched (REQ-006)
- [ ] 2.5 Validate the manifest against the app-manifest-v2 schema referenced in `src/manifest.json` `$schema` and via gate-22 manifest validation

## 3. Tests

- [ ] 3.1 Schema-configuration contract test (PHPUnit): assert the wired register's `zaak`/`taak`/`klant`/`contactmoment` schemas expose the contributed `linkedTypes` and the contactmoment `mailObjectTemplate` field map (REQ-002 template mapping)
- [ ] 3.2 PHPUnit: `alreadyLinked` consistency — a leaf-made link is observed by `KlantContactsController::searchContacts` (REQ-004)
- [ ] 3.3 Playwright e2e: zaak detail renders email/calendar/forms leaves (empty state on a fresh zaak — REQ-002/003/005 render scenarios), taak detail renders the calendar leaf, klant detail renders the contacts leaf showing an imported contact; and the graceful-degradation scenario (REQ-001) against a schema without the contributed configuration

## 4. Spec + traceability

- [ ] 4.1 Run `openspec validate leaf-integrations --strict` and resolve any errors
- [ ] 4.2 Manual verify on the dev instance: open a zaak, taak, and klant detail page; confirm each new leaf renders in both light and dark theme, the empty states are honest, and the existing five files leaves are unchanged

## 5. Quality gates and delivery

- [ ] 5.1 `composer check:strict` green; all hydra gates + gate-22 (manifest validation) green; fix any pre-existing gate issues encountered in touched files in the same batch
- [ ] 5.2 Deliver as a Codeberg PR against development (zaakafhandelapp is on the racing-PR list — NEVER direct push); land the OpenRegister-side register-configuration contribution first or in the same train so the leaves have data to render
