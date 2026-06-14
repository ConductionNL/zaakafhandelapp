---
status: proposed
---

# Zaakafhandelapp AppHost Adoption

## Purpose

Zaakafhandelapp runs its app plumbing (dashboard page, preferences, settings, admin section) on the OpenRegister AppHost generics and gains ADR-006-compliant health and metrics endpoints — which it has never had — from a declarative `observability` block in `src/manifest.json`.

**Cross-references**: `openregister/openspec/changes/apphost-observability-engine/specs/apphost-observability/spec.md`, `openregister/openspec/changes/apphost-boilerplate-controllers/`

---

## Requirements

### Requirement: Health Endpoint From Nothing

Zaakafhandelapp SHALL serve a public health endpoint at `/apps/zaakafhandelapp/api/health` through the AppHost engine, with checks `database` and `orAvailable` declared in `src/manifest.json` (the app depends on OpenRegister for all domain data).

#### Scenario: Healthy instance reports ok

- **GIVEN** a running instance with the database reachable and OpenRegister enabled
- **WHEN** `GET /apps/zaakafhandelapp/api/health` is called anonymously
- **THEN** the response MUST be HTTP 200 with `checks.database = "ok"` and `checks.orAvailable = "ok"` in the standard AppHost shape
- @e2e exclude API-only endpoint — covered by the OR AppHost Newman contract collection

#### Scenario: OpenRegister unavailable degrades health

- **GIVEN** a running instance with OpenRegister disabled
- **WHEN** `GET /apps/zaakafhandelapp/api/health` is called anonymously
- **THEN** the response MUST report the `orAvailable` check as failing per the ADR-006 status-code policy, and Nextcloud bootstrap MUST NOT fatal
- @e2e exclude API-only endpoint — covered by the OR AppHost Newman contract collection

### Requirement: Metrics Endpoint From Nothing

Zaakafhandelapp SHALL serve an admin-only Prometheus metrics endpoint at `/apps/zaakafhandelapp/api/metrics` through the AppHost engine, exposing the implicit `zaakafhandelapp_info` and `zaakafhandelapp_up` metrics plus one declared `objectCount` metric `zaakafhandelapp_zaken_total{status}` over register `zaakafhandelapp`, schema `zaak`, grouped by the zaak `status` field.

#### Scenario: Metrics exposition for admin

- **GIVEN** a seeded instance with zaken in at least two distinct statuses
- **WHEN** `GET /apps/zaakafhandelapp/api/metrics` is called by an admin
- **THEN** the response MUST be Prometheus text exposition format 0.0.4 containing `zaakafhandelapp_info` (version, php_version, nextcloud_version labels), `zaakafhandelapp_up`, and `zaakafhandelapp_zaken_total{status="..."}` series whose values match the seeded counts per status
- @e2e exclude API-only endpoint — covered by the OR AppHost Newman contract collection

#### Scenario: Metrics denied to non-admin

- **GIVEN** an authenticated non-admin user
- **WHEN** `GET /apps/zaakafhandelapp/api/metrics` is called
- **THEN** the request MUST be rejected per ADR-006 (metrics are admin-only)
- @e2e exclude API-only endpoint — covered by the OR AppHost Newman contract collection

### Requirement: Boilerplate Replaced By AppHost Generics

Zaakafhandelapp SHALL delete its local `DashboardController`, `PreferencesController`, `SettingsController`, `Settings\ZaakAfhandelAppAdmin`, and `Sections\ZaakAfhandelAppAdmin` classes and serve the equivalent behaviour through AppHost generic classes wired by `AppHost\Bootstrap::register()` and `AppHost\Routes::standard()`, with route names and URLs unchanged. Domain controllers (Zaken, Taken, Klanten, Berichten, Besluiten, ContactMomenten, Documenten, Medewerkers, Resultaten, Rollen, Statusen, ZaakAuditTrail, ZaakBesluiten, ZaakEigenschappen, ZaakInformatieObjecten, ZaakObjecten, ZaakTypen, Objects, Users, Configuration), dashboard widgets, the ZaakRegisterEventListener, and all services SHALL remain untouched.

#### Scenario: SPA page still served after adoption

- **GIVEN** the boilerplate controllers are deleted and Bootstrap wiring is in place
- **WHEN** a logged-in user opens `/apps/zaakafhandelapp/`
- **THEN** the app SPA MUST render exactly as before adoption, served by the AppHost generic dashboard controller

#### Scenario: Preferences round-trip through the generic controller

- **GIVEN** the generic preferences controller serves `/api/preferences/{key}`
- **WHEN** a user PUTs a preference value and then GETs the same key
- **THEN** the stored value MUST round-trip identically to the pre-adoption behaviour used by shared nextcloud-vue widgets
- @e2e exclude API-only endpoint — covered by the OR AppHost Newman contract collection

#### Scenario: Settings allow-list preserved

- **GIVEN** the generic settings controller serves `/settings` with the migrated `{type}_source/_schema/_register` writable-keys allow-list
- **WHEN** an admin POSTs a payload containing both allow-listed keys and an unlisted key
- **THEN** allow-listed keys MUST be persisted and the unlisted key MUST be silently ignored, matching pre-adoption behaviour
- @e2e exclude API-only endpoint — covered by the OR AppHost Newman contract collection

#### Scenario: Admin settings section still registered

- **GIVEN** the local ISettings/IIconSection classes are deleted
- **WHEN** an admin opens Nextcloud admin settings
- **THEN** the zaakafhandelapp section MUST still appear, registered by the AppHost generic admin settings (IDelegatedSettings #299 pattern)
