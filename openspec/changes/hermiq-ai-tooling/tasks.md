# Tasks: Zaakafhandelapp MCP AI Tooling

## 1. Schema-derived CRUD tools (owning register, OpenRegister side)

- [ ] 1.1 Inventory write actions per schema from `src/manifest.json` `allowCreate` flags and `appinfo/routes.php:103-107` (`objects#index/create/show/update/destroy`); record the verb matrix (all twelve schemas get `search`+`get`; write verbs only where the UI acts) in the PR description
- [ ] 1.2 Contribute `x-openregister-mcp` blocks to the owning register's schema definitions per that matrix, each verb config carrying `description`, `scope`, and honest `readOnlyHint`/`destructiveHint`/`idempotentHint` values, valid against `McpAnnotationValidator` (`openregister/lib/Service/Mcp/McpAnnotationValidator.php`)
- [ ] 1.3 PHPUnit contract test: with the contributed dialect blocks, `SchemaDerivedToolProvider` enumerates `zaakafhandelapp.{schema}.{verb}` exactly per the matrix — no write verb without a UI action (REQ-001)

## 2. Workflow tool provider (this repo)

- [ ] 2.1 Create `lib/Mcp/ZaakAfhandelAppToolProvider.php` implementing `OCA\OpenRegister\Mcp\IMcpToolProvider` (`getAppId()` = `zaakafhandelapp`); split per-domain tool logic into helper classes mirroring `decidesk/lib/Mcp/` (e.g. `McpZaakTools`, `McpKlantTools`, an argument validator); register in `lib/AppInfo/Application.php` behind a class-exists guard so a disabled OpenRegister does not fatal
- [ ] 2.2 Implement the read tools delegating to existing logic: `getZaakAuditTrail` (`zaken#getAuditTrail`), `getKlantDossier` (`klanten#getZaken/getTaken/getBerichten/getContactmomenten`)
- [ ] 2.3 Implement the write tools delegating to existing services — `closeZaak` (`ZGWZaakCloseService`), `suspendZaak`/`resumeZaak`/`extendZaak` (`ZGWZaakOpschortingVerlengingService`), `assignTaak` (taak `medewerker` update; note it triggers `MailService::sendMail`), `logContactMoment`, `sendBericht`, `linkKlantContact`/`exportKlantContact` (`KlantContactsController` logic) — each descriptor annotated scope × reach per REQ-003 (`assignTaak`, `sendBericht`, `exportKlantContact` = `reach: external`)
- [ ] 2.4 Enforce the session pass-through contract: no impersonation/elevation, ObjectService RBAC remains the invoke-time gate (ADR-063); keep every `invokeTool()` branch under the 15 s heartbeat limitation documented in `IMcpToolProvider`

## 3. Grants, approvals, audit

- [ ] 3.1 Default-deny writes: integrate the per-agent grant check (hermiq grant model) so ungranted write tools are omitted from enumeration and refused at invoke with a grant-naming error (REQ-004)
- [ ] 3.2 Approval gates: route `closeZaak`, `sendBericht`, `assignTaak`, `exportKlantContact` through the hermiq approval flow — invocation creates a pending approval; the service runs only on human confirmation; rejection/expiry leaves zero side effects (REQ-005)
- [ ] 3.3 Audit: workflow tool invocations record agent, tool id, arguments, approval decision, outcome; verify an approved `closeZaak` shows in `GET /api/zaken/{id}/audit_trail` attributable to the agent (REQ-006)
- [ ] 3.4 PHPUnit: ungranted-write refusal, granted-write-within-grant, read-tools-never-mutate (spy object service + mailer), approval-pending/confirm/reject paths, unknown-tool rejection

## 4. Chat scenarios (REQ-007)

- [ ] 4.1 Verify the three chat scenarios end-to-end on the dev instance against the AI companion: log-a-contactmoment (resolve klant + zaak via search, create pre-linked), read-only deadline briefing (no write tool offered), draft-bericht-with-human-approval; capture transcripts in the PR description

## 5. Spec + traceability

- [ ] 5.1 Add `@spec openspec/specs/ai-tooling/spec.md#REQ-00N` tags to the new `lib/Mcp/` classes
- [ ] 5.2 Run `openspec validate hermiq-ai-tooling --strict` and resolve any errors
- [ ] 5.3 Manual verify: tool enumeration on the dev instance lists the derived CRUD tools and workflow tools with correct hints; a read-only agent sees no write tools

## 6. Quality gates and delivery

- [ ] 6.1 `composer check:strict` green; all hydra gates green (SPDX headers on every new `lib/Mcp/` file); fix any pre-existing gate issues encountered in touched files in the same batch
- [ ] 6.2 Deliver as a Codeberg PR against development (zaakafhandelapp is on the racing-PR list — NEVER direct push); land the OpenRegister-side `x-openregister-mcp` contribution first or in the same train so derived tools exist when the provider ships
