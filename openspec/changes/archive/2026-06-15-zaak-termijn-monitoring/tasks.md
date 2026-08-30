# Tasks — zaak-termijn-monitoring

## 0. Dependency gate

- [~] 0.1. Confirm the OpenRegister change
      `notification-engine-scheduled-conditions` is implemented (or at least
      its dialect shape is frozen) before starting section 2; sections 1 and
      3–5 do not depend on it and may land first. NOT CONFIRMED — the OR
      change is not yet shipped, so section 2 (REQ-002) is deferred. This PR
      lands the section-1 derivation and the section-3/4 urgency surfacing,
      which have no such dependency.

## 1. Termijn derivation (REQ-001)

- [x] 1.1. Add derivation to the zaak-create path
      (`ZGWZaakLifecycleService` or a new small `ZaakTermijnService`):
      resolve the zaaktype, parse `doorlooptijd`/`servicenorm` (accept ISO
      8601 durations like `P56D` and plain day counts — the entity stores
      strings), and fill `uiterlijkeEinddatumAfdoening`/`einddatumGepland`
      from `startdatum` (fallback `registratiedatum`) only when absent.
- [x] 1.2. Never override client-supplied values; skip silently on missing
      zaaktype/terms; no derivation on update (deadline shifts are
      `zaak-opschorting-verlenging` territory).
- [x] 1.3. `@spec` tags →
      `openspec/specs/zaak-termijn-monitoring/spec.md#REQ-001`.

## 2. Declarative notification rules (REQ-002)

- [~] 2.1. Author the `x-openregister-notifications` rule block on the zaak
      schema. DEFERRED — REQ-002 depends on the OR
      `notification-engine-scheduled-conditions` change (scheduled
      date-relative conditions), which is not yet shipped; the declarative
      rules cannot fire until that condition shape exists. Tracked in the new
      spec's REQ-002 status note.
- [~] 2.2. Verify gate-18 passes. The slice adds NO notification dialect, NO
      imperative dispatch and NO BackgroundJob/cron — gate-18 is green; the
      declarative rule block itself is deferred with 2.1.
- [~] 2.3. Document the lead-window configuration key. DEFERRED with 2.1 (the
      key is only meaningful once the declarative rules exist). The urgency
      helper's lead window is already parameterised (default 7 days).

## 3. Urgency derivation helper (REQ-003)

- [x] 3.1. Add a single shared TS helper (e.g.
      `src/services/zaakUrgency.ts`): `(zaak, today) → 'op-tijd' |
      'bijna-verlopen' | 'verlopen' | null`, null for closed/undated zaken;
      unit-tested via vitest with boundary dates.
- [x] 3.2. `@spec` tags →
      `openspec/specs/zaak-termijn-monitoring/spec.md#REQ-003`.

## 4. UI surfacing (ui-case-views REQ-006, ui-dashboard-widgets REQ-006)

- [x] 4.1. Zaken list + werkvoorraad items: urgency badge (text label +
      semantic colour variables, no hardcoded colours), deadline date,
      deadline sort, overdue filter.
- [x] 4.2. Open-zaken dashboard widget: urgency indicator per item,
      most-urgent-first ordering, overdue count in the header when > 0.
- [x] 4.3. i18n: English source strings as keys, nl translations
      (e.g. `t('zaakafhandelapp', 'Overdue')` → 'Verlopen').

## 5. Tests

- [x] 5.1. PHPUnit: derivation matrix (doorlooptijd/servicenorm
      present/absent, explicit values, missing startdatum → registratiedatum
      base, unparsable duration → skip + warning log).
- [x] 5.2. vitest: urgency helper boundaries (deadline today, +7d, -1d,
      closed zaak, empty fields).
- [x] 5.3. Playwright: UI-level back-references added for ui-case-views REQ-006
      and ui-dashboard-widgets REQ-006 (gate-19 green). The live seeded-zaak run
      (overdue badge / filter / widget ordering against real data) is deferred
      with the Newman leg — no live NC here; the urgency derivation + ordering
      are fully locked by the vitest suite (zaakUrgency.spec.js).
- [~] 5.4. Newman: create-zaak round-trip asserting derived
      `uiterlijkeEinddatumAfdoening`/`einddatumGepland` values. DEFERRED —
      requires a live NC + OR + seeded zaaktype; the derivation matrix is
      locked by PHPUnit (ZaakTermijnServiceTest, 7 cases).

## 6. Quality & release

- [~] 6.1. `composer check:strict` — PHPUnit unit suite green (65 tests);
      full check:strict (psalm/phpstan) deferred to CI: the local OCP/OR stubs
      are stale so psalm/phpstan emit phantom errors here. New code is PHP-8.3
      `php -l` clean.
- [x] 6.2. Bump `appinfo/info.xml` `<version>` (bundle-affecting change —
      NC immutable cache).
- [x] 6.3. On archive, sync deltas into
      `openspec/specs/zaak-termijn-monitoring/` (new),
      `openspec/specs/ui-case-views/spec.md` (REQ-006) and
      `openspec/specs/ui-dashboard-widgets/spec.md` (REQ-006).
