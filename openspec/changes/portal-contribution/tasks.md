# Tasks — Zaakafhandelapp portal contribution ("Mijn Zaken")

- [x] 1. Verify the base is the real app (lib/, entities, OR-backed manifest), not a scaffold.
- [x] 2. Verify the scope/via/projection fields against the app data model at HEAD (`src/entities/*.ts` + the `betrokkeneIdentificatie.inpBsn` runtime dot-path in RolDetails.vue + RollenController.php).
- [x] 3. Verify the portaliq contract at HEAD: `PortalObjectReader::isValidVia` (register/schema/scopeField/targetField + optional `match`), forward vs reverse `match`, `scopeClaim` resolution, field projection.
- [x] 4. Verify the create-stamp reality: `PortalObjectWriter::createObject` stamps `scopeField = subjectRef` and does not resolve `scopeClaim` → creates deferred.
- [x] 5. Add `lib/Portal/PortalContributionProvider.php` — plain, dependency-free, duck-typed (no portaliq import, no `implements`, no info.xml dep, no constructor args); SPDX/EUPL header + `@spec` tags.
- [x] 6. Declare `getAudiences(): ['citizen']` + `getAudience(): 'citizen'` fallback + `getContribution()` branching on `$subject['audience']` (fail-closed null otherwise).
- [x] 7. Declare `citizenZaken` — FORWARD via-join over `rol` (`betrokkeneIdentificatie.inpBsn` → `zaak`, `match: 'id'`), `scopeClaim: 'bsn'`, citizen-safe `fields`.
- [x] 8. Declare `citizenTaken` — REVERSE via-join over `klant` (`bsn` → `id`, `match: 'scopeField'`) on `taak.klant`, `scopeClaim: 'bsn'`, citizen-safe `fields`.
- [x] 9. Declare `citizenBerichten` — same reverse klant via-join on `bericht.gebruikerID`, `kind: 'inbox'`, `scopeClaim: 'bsn'`, citizen-safe `fields`.
- [x] 10. Set `minTrust: 'low'` on every collection; declare empty `actions` + `notifications`.
- [x] 11. Add `tests/bootstrap.php` + `phpunit/phpunit` (require-dev) + `autoload-dev` so the unit suite runs.
- [x] 12. Add `tests/Unit/Portal/PortalContributionProviderTest.php` (direct construction, nil-UUID fixtures) covering the manifest shape, audiences, foreign-audience null, bsn/low-trust/listable, inbox, via structural validity, join directions, projection exclusions.
- [x] 13. Add the register-drift pin: assert every scopeField / via scope+target field / projected field exists on its schema's property set (dot-path aware).
- [x] 14. Write proposal.md (kind: code, refs #37, password-edge/low-trust decision, broker-deferral), design.md (scope-key + privacy note + whitelist tables + claim-names + deferred creates/documents + minTrust-raise-later + Seed Data + declarative-vs-imperative), this tasks.md, and the delta spec.
- [x] 15. Run gates in docker php:8.3-cli: repo composer checks (phpcs/phpstan/psalm) + the unit suite; fix own violations; `openspec validate portal-contribution --strict` green.

## Acceptance criteria

- The provider is inert without portaliq: no portaliq import, no `implements`, no info.xml dependency, constructible with zero arguments.
- `getAudiences()` returns `['citizen']`; a non-citizen or empty audience yields `null` from `getContribution()`.
- Exactly three read collections, all `register: 'zaakafhandelapp'`, `scopeClaim: 'bsn'`, `minTrust: 'low'`, `listable: true`; `actions` and `notifications` empty.
- Every `via` is structurally valid per portaliq `isValidVia`: non-empty string register/schema/scopeField/targetField, `match` ∈ {`id`,`scopeField`}, no nested `via`.
- Every scope / via / projected field names a real property on its schema (register-drift pin passes).
- Projections drop every staff-only, other-party and routing-internal column enumerated in design.md (`rollen`, `medewerker`, `gebruikerID`, `soortGebruiker`, org/financial/supplier internals).
- `bsn` / `inpBsn` / `gebruikerID` never appear in any `fields` whitelist.
- Repo composer checks (phpcs, phpstan, psalm) and the PHPUnit suite pass; `openspec validate portal-contribution --strict` is green.
