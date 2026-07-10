---
status: in-progress
---
# Portal Contribution — zaakafhandelapp "Mijn Zaken"

@e2e exclude inert dependency-free backend provider — zaakafhandelapp exposes no portal UI; the contribution is rendered by portaliq and verified here by PHPUnit (tests/Unit/Portal/PortalContributionProviderTest.php), not Playwright

Zaakafhandelapp's contribution to the shared portaliq external portal
(ADR-046, contract v2.2). A plain, dependency-free, duck-typed provider that
declares — for the `citizen` audience — the read surfaces a data subject may
see in their "Mijn Zaken" portal: their cases, tasks and message inbox. DigiD is
deferred; citizens authenticate via portaliq's password / `portalAccount` edge
at `low` trust, scoped by a server-managed `bsn` claim.

## ADDED Requirements

### Requirement: The contribution provider MUST be inert and dependency-free

The provider `OCA\ZaakAfhandelApp\Portal\PortalContributionProvider` MUST be a
plain class discoverable by FQCN convention and duck-typing: it MUST NOT import
portaliq, MUST NOT declare an `implements` clause, MUST NOT add an info.xml
dependency, and MUST be constructible with zero required arguments. It MUST
expose `getAudiences()`, `getAudience()` and `getContribution()`.

#### Scenario: Provider constructs and probes without portaliq
@e2e exclude backend-only — verified by PHPUnit direct construction, no UI
- GIVEN portaliq is not installed
- WHEN the class is instantiated with no arguments
- THEN construction MUST succeed
- AND `method_exists()` MUST report `getAudiences`, `getAudience` and `getContribution`
- AND the class MUST implement no interface

### Requirement: The provider MUST serve the citizen audience and fail closed otherwise

`getAudiences()` MUST return `['citizen']` and `getAudience()` MUST return
`'citizen'` (the v1 fallback). `getContribution()` MUST return the citizen
manifest when `$subject['audience'] === 'citizen'`, and MUST return `null` for
any other or missing audience.

#### Scenario: Citizen audience returns a manifest
@e2e exclude backend-only — verified by PHPUnit, no UI
- GIVEN a server-derived subject with `audience: "citizen"`
- WHEN `getContribution()` is called
- THEN it MUST return an array whose `label` is `"Mijn Zaken"`

#### Scenario: A foreign or empty audience fails closed
@e2e exclude backend-only — verified by PHPUnit, no UI
- GIVEN a subject with `audience` absent, empty, or a non-served value
- WHEN `getContribution()` is called
- THEN it MUST return `null`

### Requirement: The citizen manifest MUST declare three BSN-scoped, low-trust, read-only collections

The manifest MUST declare exactly three collections — `citizenZaken`,
`citizenTaken`, `citizenBerichten` — each with `register: "zaakafhandelapp"`,
`scopeClaim: "bsn"`, `minTrust: "low"` and `listable: true`. The manifest
`actions` and `notifications` MUST be empty (read-first). The `bsn` claim MUST be
resolved server-side from the subject's own portalAccount, never client input.

#### Scenario: Three read collections, no actions
@e2e exclude backend-only — verified by PHPUnit, no UI
- GIVEN the citizen manifest
- WHEN its collections are inspected
- THEN there MUST be exactly three, with ids `citizenZaken`, `citizenTaken`, `citizenBerichten`
- AND every collection MUST declare `scopeClaim: "bsn"`, `minTrust: "low"`, `listable: true`
- AND `actions` and `notifications` MUST both be empty

### Requirement: Mijn Zaken MUST scope by a forward rol→zaak via-join

`citizenZaken` MUST scope through a one-hop FORWARD via-join over `rol`: the join
MUST declare `scopeField: "betrokkeneIdentificatie.inpBsn"`,
`targetField: "zaak"` and `match: "id"`, so a zaak is included precisely when a
rol identifies the citizen by BSN. The via MUST be structurally valid (non-empty
string register/schema/scopeField/targetField, no nested via).

#### Scenario: Cases are owned through the citizen's rol
@e2e exclude backend-only — verified by PHPUnit, no UI
- GIVEN the `citizenZaken` collection
- WHEN its `via` is inspected
- THEN `via.schema` MUST be `"rol"`, `via.scopeField` MUST be `"betrokkeneIdentificatie.inpBsn"`, `via.targetField` MUST be `"zaak"`, `via.match` MUST be `"id"`

### Requirement: Taken and Berichten MUST scope by a reverse klant via-join, Berichten as an inbox

`citizenTaken` and `citizenBerichten` MUST each scope through a one-hop REVERSE
via-join over `klant` (`via.scopeField: "bsn"`, `via.targetField: "id"`,
`match: "scopeField"`), matched against the collection's own scope field
(`taak.klant` and `bericht.gebruikerID` respectively). `citizenBerichten` MUST
declare `kind: "inbox"`.

#### Scenario: Tasks and messages resolve through the citizen's klant
@e2e exclude backend-only — verified by PHPUnit, no UI
- GIVEN `citizenTaken` and `citizenBerichten`
- WHEN their `via` and `scopeField` are inspected
- THEN both `via` MUST be `klant` / `bsn` → `id` with `match: "scopeField"`
- AND `citizenTaken.scopeField` MUST be `"klant"` and `citizenBerichten.scopeField` MUST be `"gebruikerID"`
- AND `citizenBerichten` MUST declare `kind: "inbox"`

### Requirement: Every read MUST be field-projected to citizen-safe columns

Each collection MUST declare a `fields` whitelist that projects only citizen-safe
columns. The BSN and internal identity matchers (`inpBsn`, `bsn`, `gebruikerID`),
other-party data (`rollen`), and staff identity (`medewerker`) MUST NOT appear in
any whitelist. Every declared scope, via and projected field MUST name a real
property on its schema in the app's data model at HEAD (no register drift).

#### Scenario: Projections drop sensitive and other-party columns
@e2e exclude backend-only — verified by PHPUnit register-drift pin, no UI
- GIVEN the three collections' `fields` whitelists
- WHEN they are inspected
- THEN `rollen`, `bronorganisatie` and `klant` MUST be absent from the zaak whitelist
- AND `medewerker` and `contactmoment` MUST be absent from the taak whitelist
- AND `gebruikerID` and `soortGebruiker` MUST be absent from the bericht whitelist
- AND every scope, via and projected field MUST exist on its schema's property set

### Requirement: Creates and case documents MUST be deferred this wave

The provider MUST NOT declare create-actions, because the portaliq writer stamps
a create's scope field with the raw subjectRef pseudonym and does not resolve
`scopeClaim`, so no BSN-scoped create can be safely server-stamped. Case
documents (reachable only via a two-hop zaak→zio→eio chain) MUST NOT be declared,
because the contract permits one via-join hop only.

#### Scenario: No create-actions are exposed
@e2e exclude backend-only — verified by PHPUnit, no UI
- GIVEN the citizen manifest
- WHEN its `actions` are inspected
- THEN `actions` MUST be an empty array
- AND no collection MUST target a `document`/`enkelvoudiginformatieobject` schema
