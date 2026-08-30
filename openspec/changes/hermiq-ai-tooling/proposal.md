---
kind: code
---

# Proposal: hermiq-ai-tooling

## Why

The product line's AI posture is that **every app exposes MCP tooling for all
of its user actions**, so any action can in principle be automated by an AI
agent — and, even without automation, chat becomes a way of commanding the
app: a handler chats away while the app executes. What keeps that safe is not
the tools but the grant model: users grant rights **per agent, very
granularly** — scope (read/create/update/delete) × reach
(self/user/instance/external), writes denied by default, human approval gates
on high-impact actions, and a full audit trail. That model is shipped and in
production in hermiq: `ToolReachResolver`
(`hermiq/lib/Service/Engine/ToolReachResolver.php:60-104`) defines
`REACH_SELF` / `REACH_USER` / `REACH_INSTANCE` / `REACH_EXTERNAL` with
`READ_VERBS = ['search', 'get']`, hermiq's own descriptors carry a per-tool
`reach` (`hermiq/lib/Mcp/HermiqToolProvider.php`,
`NcMailToolDescriptors.php`), and oversight runs through
`ApprovalController`, `ToolOversightController`, and
`ToolAccessRequestService` plus the ADR-023 action-authorization matrix
(`hermiq/lib/actions.seed.json`).

The mechanism for an app to publish tools is OpenRegister's
`OCA\OpenRegister\Mcp\IMcpToolProvider`
(`openregister/lib/Mcp/IMcpToolProvider.php`): `getAppId()` / `getTools()` /
`invokeTool()`, tool ids namespaced `{appId}.{toolName}`, descriptors carrying
`inputSchema` plus the advisory `scope`, `readOnlyHint`, `destructiveHint`,
`idempotentHint` keys — with ObjectService RBAC as the sole authoritative
invoke-time gate (ADR-063) and an explicit no-impersonation contract.
Alternatively, CRUD tools are **auto-derived** from a validated
`x-openregister-mcp` block on a schema by
`openregister/lib/Mcp/BuiltIn/SchemaDerivedToolProvider.php`, emitting ids
`{appId}.{schema}.{verb}` with per-verb `scope` and hints. The fleet's
richest hand-written provider is
`decidesk/lib/Mcp/DecideskToolProvider.php` (`decidesk.listOpenActionItems`,
`decidesk.startMeeting`, …).

**Zaakafhandelapp has none of this.** There is no `lib/Mcp/` directory, no
`IMcpToolProvider` implementation, and no `x-openregister-mcp` block on any
schema it consumes. A case-handling app — the app whose daily actions
(logging a contactmoment, chasing a deadline, sending a bericht) are the most
natural chat commands in the whole fleet — is invisible to the AI companion.

## What Changes

Introduce MCP adoption from scratch, in two complementary halves that respect
the app's no-own-register constraint (`appinfo/info.xml:62-73`: all domain
data lives in OpenRegister, read and written through
`/apps/openregister/api/objects`; the app ships no schema JSON):

1. **Schema-derived CRUD tools** — contribute `x-openregister-mcp` blocks to
   the owning register's schema definitions (OpenRegister side, same
   contribution channel as `leaf-integrations`) so
   `SchemaDerivedToolProvider` auto-derives `zaakafhandelapp.{schema}.{verb}`
   tools: read verbs (`search`, `get`) for all twelve consumed schemas (zaak,
   taak, klant, contactmoment, bericht, document, besluit, resultaat, rol,
   statustype, zaaktype, medewerker); write verbs only where a real user
   action exists.
2. **A hand-written workflow provider** —
   `OCA\ZaakAfhandelApp\Mcp\ZaakAfhandelAppToolProvider implements
   IMcpToolProvider`, registered in `lib/AppInfo/Application.php`, for the
   actions that are more than CRUD, each grounded in an existing controller
   or service:
   - `zaakafhandelapp.closeZaak` — `ZGWZaakCloseService`
   - `zaakafhandelapp.suspendZaak` / `zaakafhandelapp.resumeZaak` /
     `zaakafhandelapp.extendZaak` — `ZGWZaakOpschortingVerlengingService`
   - `zaakafhandelapp.assignTaak` — taak update whose `medewerker` change
     triggers `MailService::sendMail` (an outbound email!)
   - `zaakafhandelapp.logContactMoment` — contactmoment create pre-linked to
     klant/zaak (`ContactMomentenController`)
   - `zaakafhandelapp.sendBericht` — `BerichtenController` create
   - `zaakafhandelapp.getZaakAuditTrail` — `zaken#getAuditTrail`
     (`appinfo/routes.php:42`)
   - `zaakafhandelapp.getKlantDossier` — the klant 360 reads
     (`klanten#getZaken/getTaken/getBerichten/getContactmomenten`,
     `appinfo/routes.php:48-51`)
   - `zaakafhandelapp.linkKlantContact` / `exportKlantContact` —
     `KlantContactsController` import/export (`appinfo/routes.php:59-60`)

Every descriptor separates reads from writes (`readOnlyHint` exact), and
every write is annotated with hermiq's scope × reach vocabulary. Writes are
**default-deny** per agent; `closeZaak`, `sendBericht`, `assignTaak` (it
emails), and `exportKlantContact` (data leaves the app) carry human approval
gates. Every invocation is auditable: CRUD writes land in the OpenRegister
audit trail already surfaced by `zaken#getAuditTrail` et al.; workflow tools
record the acting agent.

## Impact

- **New in this repo**: `lib/Mcp/ZaakAfhandelAppToolProvider.php` (+ helper
  classes as needed, mirroring decidesk's split into per-domain tool classes),
  registration in `lib/AppInfo/Application.php`, PHPUnit coverage.
- **Contributed outside this repo**: `x-openregister-mcp` blocks on the
  twelve consumed schemas in the owning register's definition.
- **Not changed**: controllers, services, routes — tools delegate to the
  existing services; no new HTTP surface.
- **Risk**: a write tool is a write. Mitigated structurally: ObjectService
  RBAC stays the authoritative gate (ADR-063, `IMcpToolProvider` contract),
  the session is never impersonated or elevated, writes are default-deny per
  agent, and the four high-impact tools cannot execute without a human
  approval.
- **Delivery**: zaakafhandelapp is on the racing-PR list — deliver via
  Codeberg PR against development only, never direct push.

## Dependencies

OpenRegister MCP runtime (`IMcpToolProvider`, `McpToolsService`,
`SchemaDerivedToolProvider`, the `x-openregister-mcp` dialect validated by
`McpAnnotationValidator`) and hermiq's grant/approval surfaces
(`ToolReachResolver` reach vocabulary, per-agent tool grants, approval flow)
— all shipped; this change only adopts them.
