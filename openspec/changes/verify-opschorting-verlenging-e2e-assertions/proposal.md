---
kind: code
---

# Proposal: verify-opschorting-verlenging-e2e-assertions

## Why

`openspec/specs/ui-case-views/spec.md` REQ-007 ("Suspend, resume and extend
a zaak from the case detail",
`openspec/specs/ui-case-views/spec.md:100-133`) declares four scenarios —
*Suspending from the case detail*, *Resuming shows the shifted deadlines*,
*Extending from the case detail*, *Forbidden actions are not actionable* —
each with a concrete GIVEN/WHEN/THEN describing user-visible behaviour
(open a modal, enter a reden, see the recalculated deadline, see the
action hidden when the zaaktype forbids it).

Every one of these four scenarios carries an `@e2e` tag pointing at a
Playwright test in `tests/e2e/spec-coverage/ui-case-views.spec.ts` — but
none of the four tests actually exercises the behaviour its name and
`@e2e` anchor claim to cover:

```ts
// @e2e openspec/specs/ui-case-views/spec.md#suspending-from-the-case-detail
test('suspending from the case detail — zaken view mounts with its action surface', async ({ page }) => {
	await page.goto(`${APP}/#/zaken`)
	await dismissSupportModal(page)
	await expect(page.locator('nav').filter({ hasText: 'Cases' })).toBeVisible({ timeout: 15_000 })
	await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({ timeout: 10_000 })
})
```

(`tests/e2e/spec-coverage/ui-case-views.spec.ts:127-132`, and identically
shaped for `resuming-shows-the-shifted-deadlines`
at `tests/e2e/spec-coverage/ui-case-views.spec.ts:135-141`,
`extending-from-the-case-detail` at
`tests/e2e/spec-coverage/ui-case-views.spec.ts:144-149`, and
`forbidden-actions-are-not-actionable` at
`tests/e2e/spec-coverage/ui-case-views.spec.ts:152-159`).

Every one of these four tests: navigates to `/#/zaken`, dismisses the
support modal, and asserts the left nav shows "Cases" and (in three of the
four) that an "Add" button is visible. **None of them opens a zaak's
detail view, clicks the suspend/resume/extend action, fills a reden, or
asserts a suspended state, a recalculated deadline, or a hidden/disabled
action.** This is exactly the "phantom green" failure mode: a PR can
mark REQ-007 done, the gate-19 e2e-coverage check passes (every scenario
has an `@e2e` tag pointing at an existing test), and CI is green — but the
actual UI wiring for suspend/resume/extend (does clicking "Suspend" open
`src/modals/zaken/SuspendZaak.vue`? does the reden field validate? does
the case detail re-render the recalculated
`uiterlijkeEinddatumAfdoening`? does the action actually hide when
`opschortingEnAanhoudingMogelijk` is false?) has never been exercised by
any test.

The backend deadline-shift math **is** genuinely covered:
`tests/Unit/Service/ZGWZaakOpschortingVerlengingServiceTest.php` has 11
test methods against `lib/Service/ZGWZaakOpschortingVerlengingService.php`.
The comment above the four tests
(`tests/e2e/spec-coverage/ui-case-views.spec.ts:120-124`) correctly
says as much ("the deadline-shift contract itself is locked by
PHPUnit"). But that comment then asserts the UI-level checks "confirm
the case view mounts and renders its action surface deterministically"
— which is not what REQ-007's scenarios describe, and generic
mount-smoke assertions were already covered by the earlier
`loading-the-zaken-list` scenario/test
(`tests/e2e/spec-coverage/ui-case-views.spec.ts:20-27`). The four
REQ-007 tests currently add zero incremental verification over that
first smoke test.

## What Changes

- Rewrite the four REQ-007 Playwright tests in
  `tests/e2e/spec-coverage/ui-case-views.spec.ts` to actually drive the
  described behaviour:
  - *Suspending*: open a seeded open zaak (whose zaaktype allows
    opschorting) from the zaken list, trigger the suspend action, fill
    the reden, confirm, and assert the case detail shows a suspended
    state with the reden and a start date.
  - *Resuming*: from a seeded suspended zaak, trigger resume, and assert
    the case detail shows the zaak as no longer suspended and displays
    a recalculated deadline value.
  - *Extending*: from a seeded open zaak (verlenging allowed), trigger
    extend, fill reden + a duration within the zaaktype's
    `verlengingstermijn`, confirm, and assert the shifted deadline is
    shown.
  - *Forbidden actions*: from a seeded zaak whose zaaktype forbids both
    opschorting and verlenging, open the case detail and assert the
    suspend/extend actions are absent or disabled (not merely that the
    page loaded).
- If the current seed data has no zaak/zaaktype fixture with
  verlenging/opschorting disabled, add one (or a fixture flag) so the
  *Forbidden actions* scenario has something concrete to assert against.
- Where full end-to-end seeding is genuinely impractical in the current
  Playwright harness, downgrade the affected scenario(s) to a
  reason-bearing `@e2e exclude` (per gate-19 convention) rather than
  leaving a misleading pass — but attempt the real assertion first;
  the actions and modals already exist (`src/modals/zaken/SuspendZaak.vue`,
  `src/modals/zaken/ExtendZaak.vue`) so there is no missing UI surface to
  build.

## Impact

- **Affected specs**: `ui-case-views` (REQ-007 MODIFIED — no behaviour
  change; adds an explicit e2e-fidelity clause so a mount-only test
  cannot satisfy this requirement's traceability again).
- **Affected code**: `tests/e2e/spec-coverage/ui-case-views.spec.ts`
  (REQ-007 block), possibly a seed-data fixture addition under
  `tests/e2e/` for the forbidden-actions case.
- No BREAKING changes — test-only.
