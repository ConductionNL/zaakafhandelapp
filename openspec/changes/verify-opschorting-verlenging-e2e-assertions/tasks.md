## 1. Seed fixtures for the four scenarios

- [ ] 1.1 Confirm/add a seeded zaaktype+zaak fixture where `opschortingEnAanhoudingMogelijk=true` and `verlengingMogelijk=true` (for the suspend/extend happy paths) — check existing seed data under `tests/e2e/` / whatever the `spec-coverage` project's global setup seeds.
- [ ] 1.2 Confirm/add a seeded suspended zaak (for the resume scenario) — either seed one directly or drive it via the suspend action from 1.1's fixture within the test.
- [ ] 1.3 Confirm/add a seeded zaaktype+zaak fixture where both `opschortingEnAanhoudingMogelijk=false` and `verlengingMogelijk=false` (for the forbidden-actions scenario) — this fixture does not currently exist per a repo-wide grep for `opschortingEnAanhoudingMogelijk` under `tests/`.

## 2. Rewrite the four REQ-007 tests

- [ ] 2.1 Rewrite `tests/e2e/spec-coverage/ui-case-views.spec.ts:127-132` ("suspending from the case detail"): navigate to the seeded open zaak's detail view, open the suspend modal (`src/modals/zaken/SuspendZaak.vue`), fill the reden field, submit, and assert the detail view shows a suspended indicator with the reden and a start date.
- [ ] 2.2 Rewrite `tests/e2e/spec-coverage/ui-case-views.spec.ts:135-141` ("resuming shows the shifted deadlines"): from the suspended fixture (1.2), trigger resume, and assert the suspended indicator disappears and a recalculated deadline value is displayed (compare against the pre-suspend deadline captured earlier in the test, not just presence of a date).
- [ ] 2.3 Rewrite `tests/e2e/spec-coverage/ui-case-views.spec.ts:144-149` ("extending from the case detail"): open the extend modal (`src/modals/zaken/ExtendZaak.vue`) on the fixture from 1.1, fill reden + a duration within the zaaktype's `verlengingstermijn`, submit, and assert the shown deadline shifted by the entered duration.
- [ ] 2.4 Rewrite `tests/e2e/spec-coverage/ui-case-views.spec.ts:152-159` ("forbidden actions are not actionable"): open the case detail for the fixture from 1.3, and assert the suspend and extend action entries are absent from the actions menu (or present but `disabled`), not merely that the page loaded.
- [ ] 2.5 If any of 2.1-2.4 turns out genuinely infeasible with the current Playwright harness/seed tooling (document why), replace that scenario's `@e2e` tag with a reason-bearing `@e2e exclude <reason>` per gate-19 convention instead of leaving a misleading mount-only pass.

## 3. Spec + traceability

- [ ] 3.1 Apply the `ui-case-views` REQ-007 MODIFIED delta (this change's `specs/ui-case-views/spec.md`) to the main spec via `openspec sync` (or manual merge) once the tests are rewritten.
- [ ] 3.2 Run `openspec validate verify-opschorting-verlenging-e2e-assertions --strict` and resolve any errors.
- [ ] 3.3 Run `npm run test:e2e -- ui-case-views` locally against a seeded instance and confirm all four rewritten tests fail against the current (unmodified) UI if the relevant modal/behaviour is deliberately broken (a quick red/green sanity check that the new assertions are not tautological), then confirm they pass against the real app.
