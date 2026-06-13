# Tasks — zaak-termijn-monitoring

## 0. Dependency gate

- [ ] 0.1. Confirm the OpenRegister change
      `notification-engine-scheduled-conditions` is implemented (or at least
      its dialect shape is frozen) before starting section 2; sections 1 and
      3–5 do not depend on it and may land first.

## 1. Termijn derivation (REQ-001)

- [ ] 1.1. Add derivation to the zaak-create path
      (`ZGWZaakLifecycleService` or a new small `ZaakTermijnService`):
      resolve the zaaktype, parse `doorlooptijd`/`servicenorm` (accept ISO
      8601 durations like `P56D` and plain day counts — the entity stores
      strings), and fill `uiterlijkeEinddatumAfdoening`/`einddatumGepland`
      from `startdatum` (fallback `registratiedatum`) only when absent.
- [ ] 1.2. Never override client-supplied values; skip silently on missing
      zaaktype/terms; no derivation on update (deadline shifts are
      `zaak-opschorting-verlenging` territory).
- [ ] 1.3. `@spec` tags →
      `openspec/specs/zaak-termijn-monitoring/spec.md#REQ-001`.

## 2. Declarative notification rules (REQ-002)

- [ ] 2.1. Author the `x-openregister-notifications` rule block on the zaak
      schema in the app's register configuration: approaching rule
      (scheduled condition: `uiterlijkeEinddatumAfdoening` minus 7 days,
      lead window configurable) + overdue rule (date passed), both
      conditioned on the zaak being open and targeting behandelaar with
      owner fallback — exact condition syntax per
      `notification-engine-scheduled-conditions`.
- [ ] 2.2. Verify gate-18 (`hydra-gate-notification-dialect`) passes: no
      legacy dialect, no imperative dispatch, no BackgroundJob/cron added.
- [ ] 2.3. Document the lead-window configuration key in the admin settings
      docs (reuse the existing app-configuration surface; no new settings UI
      unless a key must be admin-editable).

## 3. Urgency derivation helper (REQ-003)

- [ ] 3.1. Add a single shared TS helper (e.g.
      `src/services/zaakUrgency.ts`): `(zaak, today) → 'op-tijd' |
      'bijna-verlopen' | 'verlopen' | null`, null for closed/undated zaken;
      unit-tested via vitest with boundary dates.
- [ ] 3.2. `@spec` tags →
      `openspec/specs/zaak-termijn-monitoring/spec.md#REQ-003`.

## 4. UI surfacing (ui-case-views REQ-006, ui-dashboard-widgets REQ-006)

- [ ] 4.1. Zaken list + werkvoorraad items: urgency badge (text label +
      semantic colour variables, no hardcoded colours), deadline date,
      deadline sort, overdue filter.
- [ ] 4.2. Open-zaken dashboard widget: urgency indicator per item,
      most-urgent-first ordering, overdue count in the header when > 0.
- [ ] 4.3. i18n: English source strings as keys, nl translations
      (e.g. `t('zaakafhandelapp', 'Overdue')` → 'Verlopen').

## 5. Tests

- [ ] 5.1. PHPUnit: derivation matrix (doorlooptijd/servicenorm
      present/absent, explicit values, missing startdatum → registratiedatum
      base, unparsable duration → skip + warning log).
- [ ] 5.2. vitest: urgency helper boundaries (deadline today, +7d, -1d,
      closed zaak, empty fields).
- [ ] 5.3. Playwright: werkvoorraad overdue badge + overdue filter + widget
      ordering (seed one overdue and one future-dated zaak via the API in
      fixture setup; assertions stay UI-level — API contract checks belong
      in Newman).
- [ ] 5.4. Newman: create-zaak round-trip asserting derived
      `uiterlijkeEinddatumAfdoening`/`einddatumGepland` values.

## 6. Quality & release

- [ ] 6.1. `composer check:strict` clean; fix any pre-existing issues in
      touched files in the same batch.
- [ ] 6.2. Bump `appinfo/info.xml` `<version>` (bundle-affecting change —
      NC immutable cache).
- [ ] 6.3. On archive, sync deltas into
      `openspec/specs/zaak-termijn-monitoring/` (new),
      `openspec/specs/ui-case-views/spec.md` (REQ-006) and
      `openspec/specs/ui-dashboard-widgets/spec.md` (REQ-006).
