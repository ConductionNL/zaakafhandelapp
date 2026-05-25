## ADDED Requirements

### Requirement: The case-handling notification scope MUST be decided before any wiring
Because Zaakafhandelapp is deprecated (superseded by procest + pipelinq), the project MUST make an explicit decision on whether case-handling notifications are wired in ZAA at all, or routed to procest + pipelinq, before any notification rule is attached. The recommended outcome is to route to procest + pipelinq and NOT implement in ZAA. This change MUST NOT attach notification rules to ZAA until that decision explicitly chooses ZAA.

#### Scenario: Scope decision routes the effort to procest/pipelinq (recommended)
- GIVEN Zaakafhandelapp is deprecated and procest + pipelinq own the active case-management data model
- WHEN the notification scope decision is made
- THEN the recommended outcome MUST be to NOT implement notifications in ZAA
- AND the documented rule set MUST be carried into the procest and pipelinq notification changes instead
- AND this ZAA change MUST be closed without attaching rules to ZAA

#### Scenario: Scope decision (if it nevertheless chooses ZAA) records runtime register slugs
- GIVEN ZAA has no local register JSON and uses runtime OpenRegister registers
- WHEN the decision instead chooses to wire ZAA directly
- THEN the canonical runtime OR register slugs for `zaak`, `taak`, `besluit`, and `rol` MUST be confirmed before any rule is attached
- AND only then may the documented rules be applied

### Requirement: The case-handling notification rule set MUST be documented for completeness
For completeness, the change MUST document the case-handling notification rules that ZAA would declare via `x-openregister-notifications`, consumed by the OpenRegister `notificatie-engine`: case status transition, case closed, task assigned, task overdue, decision recorded, and role assigned. All subjects MUST be bilingual (`nl`/`en`) and metadata-only. Status-change rules MUST use the `updated` trigger with a field-change `condition` (per `notification-updated-field-change-condition`); deadline rules MUST use the `scheduled` trigger. This documentation MUST NOT be construed as an instruction to implement in ZAA.

#### Scenario: Case status transition would notify the assignee
- GIVEN the `zaak` schema (runtime OR register — slug confirmed) declares a transition rule (named ZGW status action) or an `updated`+`condition` `{"field":"status","operator":"changed"}` rule with recipients `{"kind":"field","field":"assignee"}` plus a case-handling group
- WHEN a case's status changes
- THEN the engine would deliver a notification to the case assignee and the case-handling group
- AND the subject would be available in both `nl` and `en` and contain only metadata

#### Scenario: Case closed would notify the assignee
- GIVEN the `zaak` schema declares an `updated`+`condition` `{"field":"status","operator":"equals","value":"afgehandeld"}` rule with recipients `{"kind":"field","field":"assignee"}`
- WHEN a case's status changes to `afgehandeld`
- THEN the engine would deliver a case-closed notification to the case assignee

#### Scenario: Task assigned and task overdue would notify the assignee
- GIVEN the `taak` schema declares a `created` (or `updated`+`condition` on assignee) rule and a `scheduled` deadline rule against the task due date, both with recipients `{"kind":"field","field":"assignee"}`
- WHEN a task is assigned, and separately when it passes its due date
- THEN the engine would deliver an assignment notification and an overdue notification to the task assignee respectively

#### Scenario: Decision recorded and role assigned would notify the relevant recipients
- GIVEN the `besluit` schema declares a `created` rule (→ case owner + group) and the `rol` schema declares a `created` rule (→ `{"kind":"field","field":"assignee"}`)
- WHEN a decision is recorded, and separately when a role is assigned
- THEN the engine would deliver the corresponding notifications to the case owner/group and the assigned role holder respectively
