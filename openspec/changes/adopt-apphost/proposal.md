---
kind: code
---

# Proposal: Zaakafhandelapp Adopts OpenRegister AppHost (Observability + Boilerplate)

## Problem

The 2026-06-12 fleet observability inventory classifies zaakafhandelapp as a **NO-ENDPOINT app**: it has no `HealthController`, no `MetricsController`, and no `/api/health` or `/api/metrics` routes at all — a standing ADR-006 violation. Probes and Prometheus scrapes against the app today return 404.

Beyond the missing observability, the app carries its own drifted copies of the fleet boilerplate:

- `lib/Controller/DashboardController.php` — SPA page + JSON stubs, including a leftover hardcoded `TEST_ARRAY`.
- `lib/Controller/PreferencesController.php` — generic per-user key/value preferences (CnSupportDialog et al.), byte-near-identical to 14 sibling apps.
- `lib/Controller/SettingsController.php` — appconfig get/set with a `WRITABLE_KEYS` allow-list of `{type}_source/_schema/_register` keys, the petstore SettingsService pattern inlined.
- `lib/Settings/ZaakAfhandelAppAdmin.php` — plain `ISettings` (pre-#299: no `IDelegatedSettings`).
- `lib/Sections/ZaakAfhandelAppAdmin.php` — `IIconSection` boilerplate.
- `lib/AppInfo/Application.php` — hand-written bootstrap, and `appinfo/routes.php` carries the hand-written dashboard/settings/preferences route entries.

Notably absent (so nothing to migrate): zaakafhandelapp has **no** `InitializeSettings` repair step and **no** `DeepLinkRegistrationListener` — the AppHost Bootstrap supplies both for free.

Every fleet-wide fix to this plumbing currently needs a zaakafhandelapp-specific PR; meanwhile the app fails the fleet observability contract outright.

## Proposed Change

Adopt the OpenRegister AppHost (per `apphost-observability-engine` + `apphost-boilerplate-controllers` in the openregister repo). For a NO-ENDPOINT app this is the cheapest possible adoption: there is no behaviour to preserve on the observability side — compliance arrives from nothing.

1. **Observability (minimal manifest block)**: add an `observability` block to `src/manifest.json` with:
   - `health.checks`: `database` plus `orAvailable` (the app is OR-dependent for all domain data).
   - Implicit `zaakafhandelapp_info` / `zaakafhandelapp_up` metrics — no declaration needed.
   - One worked-example metric: `zaakafhandelapp_zaken_total{status}` as an `objectCount` descriptor on register `zaakafhandelapp`, schema `zaak`, `groupBy: status` (slug resolved from the app's own `src/manifest.json` dataSources; the app ships no register JSON under `lib/Settings/`).
2. **Bootstrap**: `Application::register()` calls `AppHost\Bootstrap::register($context, 'zaakafhandelapp')`; the app keeps only its domain registrations (6 dashboard widgets, `ZaakRegisterEventListener` on the 6 OR object events).
3. **Routes**: `appinfo/routes.php` becomes `return \OCA\OpenRegister\AppHost\Routes::standard($extra)` with all ZGW/domain routes passed via `$extra`. This **adds** `/api/health` (public) and `/api/metrics` (admin, Prometheus text 0.0.4) routes served by the AppHost generic controllers.
4. **Deletions** (boilerplate only):
   - `lib/Controller/DashboardController.php`
   - `lib/Controller/PreferencesController.php`
   - `lib/Controller/SettingsController.php` (its `WRITABLE_KEYS` allow-list moves to Bootstrap options / AppHostSettingsService config)
   - `lib/Settings/ZaakAfhandelAppAdmin.php`
   - `lib/Sections/ZaakAfhandelAppAdmin.php`
   - The corresponding hand-written entries in `appinfo/routes.php` and registrations in `Application.php`.

### Explicitly untouched (domain — out of scope)

All ZGW/domain controllers stay exactly as they are: `ZakenController`, `TakenController`, `KlantenController`, `BerichtenController`, `BesluitenController`, `ContactMomentenController`, `DocumentenController`, `MedewerkersController`, `ResultatenController`, `RollenController`, `StatusenController`, `ZaakAuditTrailController`, `ZaakBesluitenController`, `ZaakEigenschappenController`, `ZaakInformatieObjectenController`, `ZaakObjectenController`, `ZaakTypenController`, `ObjectsController`, `UsersController`, `ConfigurationController`, plus `lib/Dashboard/*` widgets, `lib/EventListener/ZaakRegisterEventListener.php`, and everything under `lib/Service/`.

zaakafhandelapp is greenfield-ish on the spec side, so this change is deliberately small and self-contained: one new spec capability (`apphost-adoption`), no edits to existing specs.

## Impact

- **New behaviour**: `/apps/zaakafhandelapp/api/health` and `/api/metrics` exist for the first time → ADR-006 compliant.
- **Deleted**: 5 boilerplate PHP files (~400 lines); `Application.php` shrinks to domain-only registrations; `routes.php` to domain `$extra` routes.
- **Modified**: `src/manifest.json`, `lib/AppInfo/Application.php`, `appinfo/routes.php`.
- **Risk**: behavioural drift on the dashboard/preferences/settings endpoints the generics replace — mitigated by the OR AppHost Newman contract collection plus the app's existing e2e suite staying green. A disabled OR must not fatal NC bootstrap (AppHost alias registration is lazy by contract).
- **Delivery**: zaakafhandelapp is on the racing-PR list — deliver via Codeberg PR only, never direct push to development.

## Dependencies

Chained on the openregister changes `apphost-observability-engine` and `apphost-boilerplate-controllers` (see `hydra.json`). ADR-040 defines the manifest block; ADR-006 the endpoint contract; ADR-022 the apps-consume-OR-abstractions basis.
