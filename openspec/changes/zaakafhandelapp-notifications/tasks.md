## 1. DECISION — confirm scope before any wiring (BLOCKING)

- [ ] 1.1 Confirm whether case-handling notifications are wired in ZAA at all, or routed to procest + pipelinq. **Recommended: route to procest + pipelinq; do NOT implement in ZAA (deprecated).**
- [ ] 1.2 If the decision is "route to procest/pipelinq" (recommended): close this change without implementation and carry the documented rule set into the procest + pipelinq notification changes. STOP here.
- [ ] 1.3 If the decision nevertheless chooses ZAA: confirm the canonical runtime OpenRegister register slugs for `zaak`, `taak`, `besluit`, `rol` (ZAA has no local register JSON), and confirm `notification-updated-field-change-condition` is deployed.

## 2. Documented rule set (reference only unless 1.3 is chosen)

- [ ] 2.1 Case status transition — `transition` (ZGW status action) or `updated`+`condition` `{"field":"status","operator":"changed"}` → `{"kind":"field","field":"assignee"}` + case-handling group.
- [ ] 2.2 Case closed — `updated`+`condition` equals `afgehandeld` → `{"kind":"field","field":"assignee"}`.
- [ ] 2.3 Task assigned — `created` (or `updated`+`condition` on assignee) → `{"kind":"field","field":"assignee"}`.
- [ ] 2.4 Task overdue — `scheduled` against the task due date → `{"kind":"field","field":"assignee"}`.
- [ ] 2.5 Decision recorded — `created` → case owner + group.
- [ ] 2.6 Role assigned — `created` → `{"kind":"field","field":"assignee"}`.

## 3. If implemented (only if 1.3 chosen)

- [ ] 3.1 Apply the rules to the confirmed runtime OR register schemas; bilingual nl/en, metadata-only subjects.
- [ ] 3.2 Validate rule shapes against OpenRegister's `NotificationAnnotationValidator`.
- [ ] 3.3 Browser-verify a representative rule dispatches end-to-end.

## Acceptance criteria

- An explicit scope decision is recorded (recommended: route to procest/pipelinq, do NOT implement in ZAA).
- The case-handling rule set is documented for completeness (case transition/closed, task assigned/overdue, decision, role).
- No notification rules are attached to ZAA unless the decision explicitly chooses ZAA and confirms the runtime register slugs.
- If implemented, all subjects are bilingual (nl/en) and metadata-only; no app-local notification service code (ADR-031).
