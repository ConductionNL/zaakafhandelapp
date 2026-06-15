# Proposal — zaak-opschorting-verlenging

## Why

The ZGW Zaken API (ZRC) defines suspension and extension as first-class
lifecycle data on a zaak — `opschorting` (`{indicatie, reden}`) and
`verlenging` (`{reden, duur}`) — because they are the two legal instruments
that shift a running beslistermijn: Awb art. 4:15 suspends the clock while
the bestuursorgaan waits (e.g. for missing pieces from the aanvrager), and
Awb art. 4:14 lets it verdaag/extend the term once with notice. Missing them
means the app cannot legally represent the single most common thing that
happens to a deadline.

The gap is real on every layer:

- `openspec/specs/zgw-case-lifecycle/spec.md` covers open/close/reopen,
  confidentiality, archive-date calculation and archive validation only
  (REQ-001..005) — no suspension, no extension;
- the zaak entity (`src/entities/zaak/zaak.ts`) carries no
  `opschorting`/`verlenging` groups at all;
- yet the zaaktype entity **already models the policy switches**:
  `opschortingEnAanhoudingMogelijk`, `verlengingMogelijk` and
  `verlengingstermijn` (`src/entities/zaakTypen/zaakTypen.ts` lines 20–22) —
  fields that currently gate nothing.

`FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` lists this as
**expected-gap 2** and recommendation #3 folds it in as "the
deadline-shifting counterpart" of termijn monitoring: suspension/extension
must recalculate `einddatumGepland`/`uiterlijkeEinddatumAfdoening`, which are
exactly the fields the `zaak-termijn-monitoring` rules watch — the
notification rules need no special handling because they read the current
field values.

## What Changes

- **MODIFY** `zgw-case-lifecycle` (extend the retrofit spec, do not
  duplicate):
  - **ADDED REQ-006 — zaak opschorting**: suspend an open zaak
    (`opschorting.indicatie = true` + mandatory `reden`), gated on
    `zaaktype.opschortingEnAanhoudingMogelijk`; while suspended, record the
    suspension start; on resume (`indicatie = false`), shift
    `einddatumGepland` and `uiterlijkeEinddatumAfdoening` forward by the
    suspension duration (Awb 4:15: the clock stood still). Closed zaken and
    already-suspended zaken refuse.
  - **ADDED REQ-007 — zaak verlenging**: extend an open, non-suspended zaak
    once with a mandatory `reden` and `duur` (ISO 8601 duration), gated on
    `zaaktype.verlengingMogelijk` and capped by `zaaktype.verlengingstermijn`
    when set; shifts both termijn fields by `duur`. A second verlenging
    refuses (Awb 4:14 verdaging is single-shot).
- **MODIFY** `ui-case-views`: **ADDED REQ-007** — suspend/resume/extend
  actions on the case detail with reason modals, a visible suspension state,
  and the recalculated deadlines shown after the operation. (REQ-006 of
  `ui-case-views` is allocated by the sibling change
  `zaak-termijn-monitoring`; if that change does not land first, renumber on
  archive.)
- Extend the zaak entity/schema with the ZGW `opschorting` and `verlenging`
  groups (covered by the existing generic `domain-entities` REQ-001 parsing
  contract — no spec delta needed there).

## Impact

### Affected specs

- **MODIFIED** `specs/zgw-case-lifecycle/spec.md` — ADDED REQ-006
  (opschorting) and REQ-007 (verlenging).
- **MODIFIED** `specs/ui-case-views/spec.md` — ADDED REQ-007
  (suspend/resume/extend UI).

### Affected code

- `lib/Service/ZGWZaakLifecycleService.php` (+ validation in
  `ZGWZaakValidationService.php`) — suspension/resume bookkeeping, deadline
  shifting, verlenging single-shot guard, zaaktype policy gates.
- `lib/Controller/ZakenController.php` + `appinfo/routes.php` — the
  operations ride the existing zaak update path (ZGW models them as zaak
  PATCH data); detect `opschorting`/`verlenging` transitions in the update
  flow. No new endpoints unless the design phase decides dedicated action
  routes are cleaner for auditability.
- `src/entities/zaak/zaak.ts` (+ types/mock) — `opschorting`/`verlenging`
  groups; zaak schema in the register configuration likewise.
- `src/modals/` — `SuspendZaak` and `ExtendZaak` modals (own files, modal
  isolation); `src/views/zaken/ZaakDetails` — actions + suspension banner.
- Tests: PHPUnit (shift math, gates, refusals), Newman (round-trips),
  Playwright (UI actions), vitest (entity parsing).

### Affected behaviour

- A behandelaar can suspend a zaak with a reason; the zaak shows as
  suspended; resuming pushes both deadline dates forward by the suspended
  duration, so termijn-monitoring urgency/notifications automatically use
  the corrected deadline.
- A behandelaar can extend a zaak once within the zaaktype's policy; the
  deadlines shift by the granted duration.
- Zaaktypen that forbid suspension/extension now actually forbid it
  (`opschortingEnAanhoudingMogelijk`/`verlengingMogelijk` finally gate
  behaviour).
- All mutations remain ordinary OR object updates → the existing zaak audit
  trail records who suspended/extended what and when, for free.

### Citations

- VNG Zaken API (ZRC) 1.5.x — `Zaak.opschorting` (`indicatie`, `reden`),
  `Zaak.verlenging` (`reden`, `duur`); ZTC `opschortingEnAanhoudingMogelijk`,
  `verlengingMogelijk`, `verlengingstermijn`.
- Awb art. 4:14 (verdaging) and 4:15 (opschorting van de beslistermijn).
- `src/entities/zaakTypen/zaakTypen.ts` lines 20–22 (policy fields already
  modelled, currently unused); `src/entities/zaak/zaak.ts` (no
  opschorting/verlenging groups today).
- `openspec/specs/zgw-case-lifecycle/spec.md` REQ-001..005 (no
  suspension/extension coverage).
- `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` — expected-gap 2,
  recommendation #3.
- Sibling change: `openspec/changes/zaak-termijn-monitoring/` (watches the
  fields this change shifts).
