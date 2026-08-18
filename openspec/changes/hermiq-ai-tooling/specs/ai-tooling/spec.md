---
status: proposed
---

# Zaakafhandelapp AI Tooling — MCP for every case-handling action

**Spec refs**: `openregister/lib/Mcp/IMcpToolProvider.php` (provider
contract, ADR-063 RBAC authority, no-impersonation),
`openregister/lib/Mcp/BuiltIn/SchemaDerivedToolProvider.php`
(`x-openregister-mcp` → `{appId}.{schema}.{verb}`),
`hermiq/lib/Service/Engine/ToolReachResolver.php` (reach vocabulary),
`decidesk/lib/Mcp/DecideskToolProvider.php` (reference provider)

Every zaakafhandelapp user action becomes an MCP tool so an AI agent can, in
principle, execute it — and chat becomes a command surface for the app. The
grant model is hermiq's: scope (read/create/update/delete) × reach
(self/user/instance/external), default-deny writes, human approval gates,
audit trail. Zaakafhandelapp owns no register (`appinfo/info.xml:62-73`), so
schema-derived CRUD tools ride on `x-openregister-mcp` blocks contributed to
the owning register, while workflow tools live in a ZAA
`IMcpToolProvider`.

## ADDED Requirements

### Requirement: Schema-derived CRUD tools on every consumed schema (REQ-001)

The owning register's schema definitions SHALL gain `x-openregister-mcp`
blocks so `SchemaDerivedToolProvider` derives tools with ids
`zaakafhandelapp.{schema}.{verb}`: the read verbs `search` and `get` for all
twelve consumed schemas (zaak, taak, klant, contactmoment, bericht, document,
besluit, resultaat, rol, statustype, zaaktype, medewerker), and write verbs
(`create`, `update`, `delete`) only for schemas where the UI offers that
action today (per `src/manifest.json` `allowCreate` flags and the
`objects#create/update/destroy` routes at `appinfo/routes.php:104-107`).
Zaakafhandelapp SHALL NOT ship these blocks as schema JSON in its own
repository; they are contributed to the owning register, mirroring the
`leaf-integrations` REQ-001 mechanism. Each per-verb config SHALL declare
`scope` and the hint keys so read tools carry `readOnlyHint: true` and write
tools carry accurate `destructiveHint`/`idempotentHint` values.

#### Scenario: Read tools enumerate for all schemas

- **GIVEN** the contributed blocks are active on the wired register
- **WHEN** the MCP tool list is enumerated for a zaakafhandelapp-enabled
  instance
- **THEN** `zaakafhandelapp.zaak.search`, `zaakafhandelapp.zaak.get`, and the
  corresponding `search`/`get` pair for each of the other eleven schemas are
  present, each with `readOnlyHint: true`
- @e2e exclude MCP enumeration is backend-only — asserted via a PHPUnit contract test against SchemaDerivedToolProvider with the contributed dialect blocks

#### Scenario: No write verb without a UI action

- **GIVEN** a schema whose detail/index pages offer no create action (e.g. a
  read-only computed view)
- **WHEN** the derived tool list for that schema is inspected
- **THEN** it contains no `create`, `update`, or `delete` tool for that
  schema
- @e2e exclude backend-only — PHPUnit assertion comparing derived verbs against the manifest's allowCreate flags

### Requirement: A ZAA workflow tool provider for actions beyond CRUD (REQ-002)

Zaakafhandelapp SHALL ship
`OCA\ZaakAfhandelApp\Mcp\ZaakAfhandelAppToolProvider` implementing
`OCA\OpenRegister\Mcp\IMcpToolProvider`, registered in
`lib/AppInfo/Application.php`, with `getAppId()` returning
`zaakafhandelapp` and every descriptor id prefixed `zaakafhandelapp.`. The
provider SHALL expose one tool per workflow action, each delegating to the
existing service or controller logic (never reimplementing it):
`closeZaak` (`ZGWZaakCloseService`), `suspendZaak` / `resumeZaak` /
`extendZaak` (`ZGWZaakOpschortingVerlengingService`), `assignTaak` (taak
`medewerker` update, which triggers `MailService::sendMail`),
`logContactMoment` (contactmoment create pre-linked to klant and/or zaak),
`sendBericht` (bericht create), `getZaakAuditTrail`
(`zaken#getAuditTrail`), `getKlantDossier` (the
`klanten#getZaken/getTaken/getBerichten/getContactmomenten` reads),
`linkKlantContact` and `exportKlantContact` (`KlantContactsController`).
Tool invocations SHALL run as the current user's session — never
impersonated or elevated — with ObjectService RBAC as the authoritative
invoke-time gate per the `IMcpToolProvider` contract.

#### Scenario: Provider enumerates the workflow tools

- **GIVEN** the app is enabled
- **WHEN** `getTools()` is called on the registered provider
- **THEN** the descriptor list contains exactly the workflow tools above,
  every id starts with `zaakafhandelapp.`, and every descriptor carries an
  `inputSchema`
- @e2e exclude backend-only — PHPUnit contract test on the provider

#### Scenario: Unknown tool id is rejected

- **GIVEN** the registered provider
- **WHEN** `invokeTool('zaakafhandelapp.doesNotExist', [])` is called
- **THEN** the call MUST fail with an explicit unknown-tool error and no side
  effect
- @e2e exclude backend-only — PHPUnit

### Requirement: Reads separated from writes, every write annotated scope × reach (REQ-003)

Every tool descriptor SHALL be classified: read tools (`getZaakAuditTrail`,
`getKlantDossier`, all derived `search`/`get`) carry `readOnlyHint: true`;
write tools carry `readOnlyHint: false` plus an accurate
`destructiveHint`/`idempotentHint` pair and a hermiq-vocabulary annotation of
scope (`read`/`create`/`update`/`delete`) × reach (`self`/`user`/`instance`/
`external` per `ToolReachResolver::ORDER`). Reach SHALL be honest about
side-effect blast radius: `assignTaak` and `sendBericht` are
`reach: external` (they cause email / citizen-facing correspondence),
`exportKlantContact` is `reach: external` (data leaves the app into the
user's address book and onward sync), `closeZaak` / `suspendZaak` /
`resumeZaak` / `extendZaak` / `logContactMoment` / `linkKlantContact` are
`reach: instance` or narrower.

#### Scenario: Read tool never mutates

- **GIVEN** any tool with `readOnlyHint: true`
- **WHEN** it is invoked with valid arguments
- **THEN** no object create/update/delete occurs through
  `/apps/openregister/api/objects` and no mail is sent
- @e2e exclude backend-only — PHPUnit with a spy object service and mailer

#### Scenario: Email-causing write is declared external

- **GIVEN** the `zaakafhandelapp.assignTaak` descriptor
- **WHEN** its annotations are inspected
- **THEN** it declares a write scope (`update`) and `reach: external`,
  because changing `medewerker` triggers `MailService::sendMail`
- @e2e exclude backend-only — PHPUnit descriptor assertion

### Requirement: Writes are default-deny per agent (REQ-004)

No write tool SHALL be invocable by an agent that has not been explicitly
granted that tool's scope × reach by a human. The default grant set for a
new agent SHALL contain read tools at most; every write grant is an explicit,
per-agent, per-tool opt-in following hermiq's grant model (per-agent tool
grants + the ADR-023 action-authorization pattern of
`hermiq/lib/actions.seed.json`). Denial SHALL be advisory at enumeration
(ungranted write tools are not offered to the agent) and authoritative at
invocation (an ungranted invoke is refused before any service call).

#### Scenario: Ungranted write refused at invoke time

- **GIVEN** an agent with only read grants
- **WHEN** the agent attempts `zaakafhandelapp.logContactMoment`
- **THEN** the invocation is refused before `ContactMomentenController`
  logic runs, and the refusal names the missing grant
- @e2e exclude backend-only — PHPUnit

#### Scenario: Granted write executes within its grant only

- **GIVEN** an agent granted `logContactMoment` (create × instance) but not
  `sendBericht`
- **WHEN** the agent logs a contactmoment and then attempts a bericht
- **THEN** the contactmoment is created and the bericht attempt is refused

### Requirement: Human approval gates on high-impact actions (REQ-005)

`closeZaak`, `sendBericht`, `assignTaak`, and `exportKlantContact` SHALL be
approval-gated: even when granted, an agent's invocation produces a pending
approval (hermiq approval-flow pattern) that a human must confirm before the
underlying service executes. Closing a case ends a citizen's legal procedure
(`ZGWZaakCloseService` also drives archiving via the ZGW archive regime);
`sendBericht` and `assignTaak` emit outbound correspondence;
`exportKlantContact` moves personal data out of the app. A rejected or
expired approval SHALL leave no side effect.

#### Scenario: Close-zaak waits for a human

- **GIVEN** an agent granted `closeZaak`
- **WHEN** the agent invokes it for an open zaak
- **THEN** the zaak remains open, a pending approval is created naming the
  agent, the tool, and the target zaak, and only after human confirmation
  does `ZGWZaakCloseService` run

#### Scenario: Rejection leaves no side effect

- **GIVEN** a pending `sendBericht` approval
- **WHEN** the human rejects it
- **THEN** no bericht object is created and no mail is sent, and the
  rejection is recorded
- @e2e exclude backend-only — PHPUnit on the approval flow adapter

### Requirement: Every invocation is audited (REQ-006)

Every tool invocation — read or write, approved or refused — SHALL be
auditable: derived CRUD writes land in the OpenRegister audit trail already
exposed per resource (`zaken#getAuditTrail` et al.,
`appinfo/routes.php:42-45`), and workflow tools SHALL record the acting
agent, the tool id, the arguments, the approval decision where applicable,
and the outcome, so a handler reviewing a zaak's audit trail can distinguish
agent-performed actions from human ones.

#### Scenario: Agent write visible in the zaak audit trail

- **GIVEN** an approved agent invocation of `closeZaak`
- **WHEN** a handler opens the zaak's audit trail
  (`GET /api/zaken/{id}/audit_trail`)
- **THEN** the closing entry is present and attributable to the agent acting
  for the granting user

### Requirement: Chat commands the app (REQ-007)

With the tools above enumerated to the AI companion, the following
domain conversations SHALL be executable end-to-end, demonstrating chat as a
command surface rather than an automation add-on.

#### Scenario: Log a contactmoment from chat

- **GIVEN** a handler chatting with an agent granted `logContactMoment` and
  read tools
- **WHEN** the handler writes "Log a phone contactmoment for klant Jansen
  about her passport case: she asked for a status update"
- **THEN** the agent resolves the klant via `zaakafhandelapp.klant.search`,
  the zaak via `zaakafhandelapp.zaak.search`, invokes `logContactMoment`
  with `kanaal`, `notitie`, and the klant/zaak links populated, and the
  contactmoment appears on the klant's detail page

#### Scenario: Deadline briefing without any write grant

- **GIVEN** an agent with read grants only
- **WHEN** the handler asks "Which of my cases breach their
  uiterlijkeEinddatumAfdoening this week, and what open tasks do they have?"
- **THEN** the agent answers from `zaakafhandelapp.zaak.search` and
  `zaakafhandelapp.taak.search` alone, and no write tool is offered or
  invoked

#### Scenario: Draft a bericht, human presses send

- **GIVEN** an agent granted the approval-gated `sendBericht`
- **WHEN** the handler says "Tell the applicant of zaak Z2026-0142 that we
  received the missing document and the case resumes"
- **THEN** the agent composes and invokes `sendBericht`, the handler sees a
  pending approval with the drafted message, and only after confirmation is
  the bericht created and delivered
