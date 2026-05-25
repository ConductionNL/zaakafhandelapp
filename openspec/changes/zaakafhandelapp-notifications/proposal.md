---
kind: config
depends_on: [notification-updated-field-change-condition]
---

# Zaakafhandelapp Notifications

## ⚠ ZAA IS DEPRECATED — recommendation: do NOT implement here

Zaakafhandelapp is **deprecated**, superseded by **procest** (case management /
zaakgericht-werken) and **pipelinq** (CRM / requests). The fleet notification
analysis (`hydra/openspec/fleet-notification-plan.md`, zaakafhandelapp row)
flags it explicitly: *"confirm whether to wire at all vs route to
procest/pipelinq."*

**Recommendation: do NOT add notification wiring to ZAA.** Route all
case-handling notification effort to procest + pipelinq, which carry the active
data model and are greenfield-ready in the same plan. This change request exists
to **document the would-be rule set for completeness** and to force an explicit
scope decision — not as an endorsement to implement. The first task in tasks.md
is a DECISION task; if the decision is "route to procest/pipelinq" (the
recommendation), this change is closed without implementation and the rules
below are carried into the procest/pipelinq notification changes instead.

ZAA has **no local register JSON** — it is a ZGW case-handling front-end over
**runtime OpenRegister registers**. So the schema slugs below are marked
**"(runtime OR register — confirm slugs)"**: there is no in-repo schema file to
annotate; rules would have to be applied to the runtime register schemas, which
is itself an argument for routing the effort to the apps that own those schemas.

## Why (the underlying need, to be served by procest/pipelinq)

Municipal case workers need to know when a case transitions, a case is closed, a
task is assigned or overdue, a decision is recorded, or a role is assigned.
These are real needs — but in the deprecated app they have no durable home.

## Would-be rule set (documented for completeness; target = procest/pipelinq)

Declared as `x-openregister-notifications`, subjects bilingual nl/en,
metadata-only. Status-change rules use `updated`+`condition` (per
`notification-updated-field-change-condition`).

- **Case status transition** (`zaak` runtime OR register — confirm slug):
  `transition` (named ZGW status action) **or** `updated`+`condition`
  `{"field":"status","operator":"changed"}` → `{"kind":"field","field":"assignee"}`
  + case-handling group.
- **Case closed** (`zaak`): `updated`+`condition`
  `{"field":"status","operator":"equals","value":"afgehandeld"}` →
  `{"kind":"field","field":"assignee"}` + case-handling group.
- **Task assigned** (`taak` runtime OR register — confirm slug): `created` (or
  `updated`+`condition` on assignee) → `{"kind":"field","field":"assignee"}`.
- **Task overdue** (`taak`): `scheduled` deadline check against the task due
  date → `{"kind":"field","field":"assignee"}`.
- **Decision recorded** (`besluit` runtime OR register — confirm slug):
  `created` → case owner + relevant group.
- **Role assigned** (`rol` runtime OR register — confirm slug): `created` →
  `{"kind":"field","field":"assignee"}`.

## Capabilities

### Added Capabilities

- `notifications`: documents the case-handling notification rule set that ZAA
  *would* declare (case transition/closed, task assigned/overdue, decision,
  role). **Recommended NOT to implement in ZAA (deprecated); route to procest +
  pipelinq.** Gated on an explicit scope decision.

## Impact

- **Recommendation:** no code/config change in ZAA. The rules are carried into
  procest + pipelinq notification changes instead.
- **If ZAA were wired anyway:** rules would attach to runtime OR register
  schemas (no in-repo register JSON), with slugs confirmed at apply time —
  another reason the recommendation is to route elsewhere.
- **Engine dependency:** status-change rules require
  `notification-updated-field-change-condition` (archived in openregister).
- **No** external-email channel needed — recipients are internal uids/groups.
