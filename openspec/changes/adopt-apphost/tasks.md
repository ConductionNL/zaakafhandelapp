# Tasks: Zaakafhandelapp Adopts AppHost

## 0. Absence baseline

- [ ] 0.1 Confirm the NO-ENDPOINT baseline on the dev instance: `curl /apps/zaakafhandelapp/api/health` and `/api/metrics` both return 404 today; record the responses in the PR description (there is no prior output to preserve — adoption is from-nothing-to-compliant)

## 1. Manifest observability block (minimal)

- [ ] 1.1 Add `observability` block to `src/manifest.json`: `health.checks` = `database` + `orAvailable`; one `objectCount` metric descriptor `zaakafhandelapp_zaken_total` on register `zaakafhandelapp`, schema `zaak`, `groupBy: status` (implicit `zaakafhandelapp_info`/`zaakafhandelapp_up` need no declaration)
- [ ] 1.2 Validate via ManifestService diagnostics (no errors) and gate-22 manifest validation

## 2. Bootstrap, routes, deletions

- [ ] 2.1 `Application::register()` calls `AppHost\Bootstrap::register($context, 'zaakafhandelapp')`; keep only domain registrations (6 dashboard widgets, ZaakRegisterEventListener on the 6 OR object events); verify a disabled OR does not fatal NC bootstrap
- [ ] 2.2 Rewrite `appinfo/routes.php` as `return \OCA\OpenRegister\AppHost\Routes::standard($extra)` with all ZGW/domain resources + routes in `$extra`; new `/api/health` (public) and `/api/metrics` (admin) routes resolve to the AppHost generic controllers
- [ ] 2.3 Delete boilerplate: `lib/Controller/DashboardController.php`, `lib/Controller/PreferencesController.php`, `lib/Controller/SettingsController.php`, `lib/Settings/ZaakAfhandelAppAdmin.php`, `lib/Sections/ZaakAfhandelAppAdmin.php`; migrate the SettingsController `WRITABLE_KEYS` allow-list into the AppHost settings configuration
- [ ] 2.4 Sweep references (tests, docs, `@spec` tags) to the deleted classes; domain controllers and services untouched

## 3. Verification

- [ ] 3.1 OR AppHost Newman contract collection green against zaakafhandelapp: health is public 200 with standard shape (`database` + `orAvailable` ok), metrics is admin-only Prometheus text 0.0.4 containing `zaakafhandelapp_info`, `zaakafhandelapp_up`, and `zaakafhandelapp_zaken_total{status}` matching seeded zaak counts
- [ ] 3.2 Existing zaakafhandelapp e2e suite green (dashboard page, settings, preferences behaviour unchanged through the generics)

## 4. Docs

- [ ] 4.1 Update app docs: observability endpoints now exist (URLs, auth posture, metric list); note the app runs on the OpenRegister AppHost and link the manifest `observability` block

## 5. Quality gates and delivery

- [ ] 5.1 `composer check:strict` green; all 18 hydra gates + gate-22 (manifest validation) green; fix any pre-existing gate issues encountered in touched files in the same batch
- [ ] 5.2 Deliver as a Codeberg PR against development (zaakafhandelapp is on the racing-PR list — NEVER direct push; orchestration force-resets the branch)
