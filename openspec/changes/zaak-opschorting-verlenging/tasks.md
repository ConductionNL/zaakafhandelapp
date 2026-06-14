# Tasks — zaak-opschorting-verlenging

## 1. Entity & schema groundwork

- [ ] 1.1. Add the ZGW `opschorting` (`indicatie`, `reden`, plus an
      app-internal suspension start date) and `verlenging` (`reden`, `duur`)
      groups to `src/entities/zaak/zaak.ts` (+ `zaak.types.ts`,
      `zaak.mock.ts`) and to the zaak schema in the app's register
      configuration.
- [ ] 1.2. Decide in design where the suspension start lives (ZGW has no
      field for it — an app-managed property such as
      `opschorting.eerdereOpschorting`-style bookkeeping or a private
      `_opschortingGestart`); record the decision in design.md.

## 2. Lifecycle service (REQ-006, REQ-007)

- [ ] 2.1. `ZGWZaakLifecycleService`: detect `opschorting`/`verlenging`
      transitions in the zaak update flow (the same hook that drives
      open/close/reopen on status change today).
- [ ] 2.2. Suspension: validate (zaaktype allows it, zaak open, not already
      suspended, reden non-empty), record the start date.
- [ ] 2.3. Resume: compute elapsed suspension duration, shift
      `einddatumGepland` + `uiterlijkeEinddatumAfdoening` forward by it,
      clear the start-date bookkeeping, keep the last reden.
- [ ] 2.4. Verlenging: validate (zaaktype allows it, `duur` ≤
      `verlengingstermijn` when set, zaak open + not suspended, no prior
      verlenging, reden non-empty), shift both deadline fields by `duur`,
      persist the group. ISO 8601 duration parsing shared with
      `zaak-termijn-monitoring` task 1.1 where applicable.
- [ ] 2.5. Validation lives in `ZGWZaakValidationService` /
      `ZGWZaakLifecycleService` and raises errors — never persists an
      invalid state (matches REQ-005 style).
- [ ] 2.6. `@spec` tags →
      `openspec/specs/zgw-case-lifecycle/spec.md#REQ-006` / `#REQ-007`.

## 3. UI (ui-case-views REQ-007)

- [ ] 3.1. New `src/modals/zaken/SuspendZaak.vue` (reden required) and
      `src/modals/zaken/ExtendZaak.vue` (reden + duration, max from
      `verlengingstermijn`) — own files per modal-isolation gate; NcSelect
      usages carry `inputLabel`.
- [ ] 3.2. Case detail: suspend/resume/extend actions, hidden/disabled with
      explanatory label when the zaaktype policy or lifecycle state forbids
      them; suspension banner (reden + start date); refreshed deadline dates
      after resume/extend.
- [ ] 3.3. i18n: English source strings as keys
      (e.g. `t('zaakafhandelapp', 'Suspend case')`), nl translations.

## 4. Tests

- [ ] 4.1. PHPUnit: shift math (resume after N days, verlenging duur),
      refusal matrix (closed, suspended, double-verlenging, policy false,
      empty reden, duur > verlengingstermijn), ISO duration parsing.
- [ ] 4.2. Newman: suspend → resume round-trip asserting shifted dates;
      verlenging round-trip; refusal cases assert 400 and unchanged zaak.
- [ ] 4.3. Playwright: suspend via modal → banner visible; resume → banner
      gone + new deadline shown; extend via modal → new deadline shown;
      forbidden zaaktype → actions disabled. (UI-level assertions only; the
      API contract lives in Newman.)
- [ ] 4.4. vitest: zaak entity parses/serialises the new groups.

## 5. Quality & release

- [ ] 5.1. `composer check:strict` clean; fix pre-existing issues in touched
      files in the same batch.
- [ ] 5.2. Bump `appinfo/info.xml` `<version>`.
- [ ] 5.3. On archive, sync deltas into
      `openspec/specs/zgw-case-lifecycle/spec.md` (REQ-006/007) and
      `openspec/specs/ui-case-views/spec.md` (REQ-007 — renumber if
      `zaak-termijn-monitoring` has not landed its REQ-006 yet).
- [ ] 5.4. Cross-check with `zaak-termijn-monitoring`: after a resume or
      extension the urgency derivation and the OR notification rules pick up
      the shifted dates without any extra wiring (they read current field
      values).
