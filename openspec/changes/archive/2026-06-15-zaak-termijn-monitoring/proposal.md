# Proposal — zaak-termijn-monitoring

## Why

Statutory response deadlines (behandeltermijnen, fatale termijnen) are the #1
operational concern in Dutch municipal case handling: the Awb attaches legal
consequences (dwangsom, beroep bij niet tijdig beslissen) to a missed
beslistermijn. The data model already carries everything needed — `zaak` has
`startdatum`, `einddatumGepland` and `uiterlijkeEinddatumAfdoening`
(`src/entities/zaak/zaak.ts` lines 17–20), and `zaaktype` has `doorlooptijd`
and `servicenorm` (`src/entities/zaakTypen/zaakTypen.ts` lines 18–19) — but
**nothing watches these fields**:

- no notification fires when a deadline approaches or passes;
- the werkvoorraad and case lists do not flag overdue or at-risk zaken;
- the dashboard widgets (`openspec/specs/ui-dashboard-widgets/spec.md`) show
  open zaken with no urgency signal;
- the deadline fields are not even derived — a zaak created without explicit
  dates simply has empty termijn fields, even when its zaaktype defines a
  doorlooptijd.

`FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` lists this as
**expected-gap 1** and **recommendation #3**: "Build deadline monitoring on
the OR notification engine — highest user value for case workers and the
natural first *forward* (non-retrofit) spec for this greenfish-ish app."

Per company constraint the app does **not** build its own scheduler, cron job
or notification engine: deadline rules are declared on the zaak schema via the
OpenRegister notification dialect (ADR-031, `x-openregister-notifications`)
and evaluated by OpenRegister. Date-based ("the deadline is N days away",
"the deadline has passed") conditions are new engine capability:

> **Dependency:** this change depends on the OpenRegister change
> `notification-engine-scheduled-conditions`
> (`openregister/openspec/changes/notification-engine-scheduled-conditions/`,
> being authored 2026-06-11), which adds scheduled, date-field-relative
> conditions to the OR notification engine. The declarative rules in REQ-002
> cannot fire until that change ships; the derivation (REQ-001) and the UI
> surfacing (REQ-003 + the ui deltas) have no such dependency and can land
> first.

## What Changes

- **ADD** capability `zaak-termijn-monitoring`:
  - **Derive termijn fields on zaak creation** (REQ-001): when a zaak is
    created without explicit dates and its zaaktype defines the terms,
    `uiterlijkeEinddatumAfdoening` is derived from `startdatum` +
    `zaaktype.doorlooptijd` (the legal term) and `einddatumGepland` from
    `startdatum` + `zaaktype.servicenorm` (the service target). Explicit
    client-supplied dates are never overridden.
  - **Declare deadline notification rules** (REQ-002): approaching-deadline
    and overdue rules on the zaak schema via the ADR-031 declarative dialect,
    using the scheduled date-relative conditions from
    `notification-engine-scheduled-conditions`. Rules target the assigned
    behandelaar (object owner fallback) and only fire for open zaken.
  - **Derive a deadline-urgency state** (REQ-003): a single shared derivation
    (op-tijd / bijna-verlopen / verlopen) from today vs the termijn fields,
    used by every UI surface so list, werkvoorraad and widget agree.
- **MODIFY** `ui-case-views`: ADDED REQ-006 — case lists and the personal
  werkvoorraad surface deadline urgency (overdue badge, approaching warning,
  sort by deadline, overdue filter).
- **MODIFY** `ui-dashboard-widgets`: ADDED REQ-006 — the open-zaken dashboard
  widget surfaces deadline urgency and brings the most urgent zaken to the
  top.

Out of scope: shifting deadlines via opschorting/verlenging — that is the
companion change `zaak-opschorting-verlenging` (which recalculates the same
fields this change watches; the notification rules need no knowledge of it
because they read the *current* field values).

## Impact

### Affected specs

- **ADDED** `specs/zaak-termijn-monitoring/spec.md` — new capability
  (REQ-001 derivation, REQ-002 declarative notification rules, REQ-003
  urgency derivation).
- **MODIFIED** `specs/ui-case-views/spec.md` — ADDED REQ-006 (deadline
  urgency in lists/werkvoorraad).
- **MODIFIED** `specs/ui-dashboard-widgets/spec.md` — ADDED REQ-006
  (deadline urgency in the open-zaken widget).

### Affected code

- `lib/Service/ZGWZaakLifecycleService.php` (or a small new
  `ZaakTermijnService`) — termijn derivation on create.
- The zaak schema in the app's OpenRegister register configuration —
  `x-openregister-notifications` rule block (ADR-031 dialect; declarative
  JSON only, no imperative dispatch).
- `src/services/` — shared deadline-urgency derivation helper (TS).
- `src/views/zaken/*` + werkvoorraad list components — urgency badge, sort,
  overdue filter.
- `src/views/widgets/*` (open-zaken widget) — urgency flag + ordering.
- `tests/Unit`, vitest, Playwright, Newman — new coverage.

### Affected behaviour

- New zaken get real termijn dates instead of empty strings whenever the
  zaaktype defines terms; existing zaken are untouched (no backfill).
- Behandelaars receive a Nextcloud notification N days before
  `uiterlijkeEinddatumAfdoening` and again when it passes while the zaak is
  still open — produced by OpenRegister, not by app code or cron.
- Case workers see at a glance which zaken are overdue or at risk, in the
  list views, the werkvoorraad and the dashboard widget.

### Citations

- `src/entities/zaak/zaak.ts` lines 17–20 (`startdatum`, `einddatumGepland`,
  `uiterlijkeEinddatumAfdoening`); `src/entities/zaakTypen/zaakTypen.ts`
  lines 18–19 (`doorlooptijd`, `servicenorm`).
- ADR-031 (`hydra/openspec/` — declarative `x-openregister-notifications`
  dialect; gate-18 forbids the legacy dialect and imperative dispatch).
- OpenRegister change `notification-engine-scheduled-conditions` (authored
  2026-06-11) — scheduled date-relative conditions (hard dependency for
  REQ-002).
- VNG ZTC: `doorlooptijd` = wettelijke afhandeltermijn,
  `servicenorm` = streeftermijn; Awb art. 4:13–4:15 (beslistermijnen).
- `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` — expected-gap 1,
  recommendation #3.
