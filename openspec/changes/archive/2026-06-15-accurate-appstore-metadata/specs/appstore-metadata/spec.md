# Capability: appstore-metadata

@e2e exclude metadata/documentation-only capability — no UI surface; verified by xmllint + grep checks, not Playwright

App Store listing (`appinfo/info.xml`) and repository README accuracy: the
published metadata SHALL describe the product that ships, and SHALL NOT carry
operational or technology claims the codebase does not back.

## ADDED Requirements

### Requirement: Listing describes the actual product (REQ-META-001)

The `appinfo/info.xml` `<summary>` and `<description>` SHALL describe Zaak
Afhandel App as Dutch municipal case handling (*zaakgericht werken*, VNG
GEMMA/ZGW) built on OpenRegister — covering cases (zaken) with zaaktypen,
statussen, rollen, besluiten and resultaten; tasks (taken); klanten, personen
and organisaties with contactmoment logging; in-case messaging with a message
audit trail; a personal werkvoorraad dashboard; and the ZGW (ZRC/ZTC/DRC/BRC)
REST API surface. Both elements SHALL be provided in English and in a Dutch
`lang="nl"` variant, SHALL contain no text describing OpenCatalogi, gateway /
service-bus functionality, cloud events, or API mapping/translation, and the
summary SHALL be a complete, untruncated sentence.

#### Scenario: Description matches the shipped feature set

- **GIVEN** the `appinfo/info.xml` of a release
- **WHEN** the `<summary>` and `<description>` are read
- **THEN** every feature claim in them maps to an implemented surface covered
  by a spec under `openspec/specs/`
- **AND** the strings "gateway", "service bus", "cloud event", "OpenCatalogi"
  and "federated catalogi" do not occur in either element.

#### Scenario: Dutch variants present

- **WHEN** `appinfo/info.xml` is validated
- **THEN** a `<summary lang="nl">` and `<description lang="nl">` exist and
  carry the same product claims as the English elements.

### Requirement: No unbacked operational requirements (REQ-META-002)

The listing SHALL NOT declare operational requirements the code does not
have. In particular it SHALL NOT claim that System Cron is required while the
app registers no background jobs (no `BackgroundJob`/Cron classes under
`lib/`).

#### Scenario: Cron claim absent

- **GIVEN** `lib/` contains no `OCP\BackgroundJob` implementations
- **WHEN** `appinfo/info.xml` is read
- **THEN** it contains no statement that System Cron is required.

### Requirement: No unbacked technology claims (REQ-META-003)

The listing and README SHALL NOT claim Elasticsearch-backed full-text case
search. Search SHALL be described as provided by OpenRegister (the
foundation), matching the company constraint that search is not
re-implemented per app. Pre-existing elastic *config keys*
(`app-configuration` spec) MAY remain but SHALL NOT be marketed as a search
feature.

#### Scenario: Elasticsearch removed from promise surface

- **WHEN** `appinfo/info.xml` and `README.md` are read
- **THEN** neither presents Elasticsearch as a (optional) search backend
- **AND** the README Tech Stack search row attributes search to OpenRegister.

### Requirement: README promises match the implemented surface (REQ-META-004)

The README feature list and architecture diagram SHALL only promise
implemented behaviour: the Message Audit Trail bullet SHALL NOT promise
"revert capability" (only the audit history read exists,
`zgw-client-interaction` REQ-004), and the architecture diagram SHALL NOT
show "Cron → background jobs" or "Nextcloud Activity" nodes while no backing
code exists.

#### Scenario: Revert promise removed

- **WHEN** the README Features section is read
- **THEN** the Message Audit Trail bullet describes edit history only, with
  no revert claim.

#### Scenario: Diagram nodes backed by code

- **WHEN** the README architecture diagram is read
- **THEN** every node corresponds to an existing integration (Vue frontend,
  PHP controllers, services, Nextcloud DB/OpenRegister, Nextcloud Dashboard)
- **AND** Elasticsearch, Cron and Nextcloud Activity nodes are absent.
