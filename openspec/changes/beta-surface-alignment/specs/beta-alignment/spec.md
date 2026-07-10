## ADDED Requirements

### Requirement: Cross-surface vocabulary agreement
Zaakafhandelapp's four public surfaces (`appinfo/info.xml`, `src/manifest.json`,
the conduction.nl product page, and `docs/`) SHALL describe the same canonical
feature list, using the same terms (zaken, taken, klanten, contactmomenten,
berichten, opschorting/verlenging, ZGW REST API surface, werkvoorraad
dashboard, audit trail), and SHALL NOT diverge on the app version or
license identifier.

#### Scenario: Version consistency
- **WHEN** the app version in `appinfo/info.xml` changes
- **THEN** `publiccode.yml` `softwareVersion` and the conduction.nl product
  page's hero version SHALL be updated to match in the same change

#### Scenario: License consistency
- **WHEN** `appinfo/info.xml` declares a `<licence>`
- **THEN** it SHALL match the license already declared in `LICENSE`, the
  README badge, and `publiccode.yml` `legal.license` (EUPL-1.2)

### Requirement: No unverified marketing/compliance claims
Any feature, integration, or compliance claim made on the conduction.nl
product page, in `publiccode.yml`, or in `docs/` SHALL be verifiable against
`lib/` or `src/` at the time it is published. Claims about features that are
blocked, planned, or dependent on components this app does not integrate
with (e.g. DigiD/eIDAS login, a citizen-facing portal, Calendar/Deck
integration, formal TMLO/Archiefwet certification, integration with the
external Open Zaak product) SHALL NOT be published until the underlying
code exists and is wired.

#### Scenario: Citizen-portal / DigiD claim gating
- **WHEN** the product page or publiccode.yml is edited
- **THEN** it SHALL NOT claim a citizen-facing case-status portal or
  DigiD/eIDAS login unless `lib/Controller` contains a `#[PublicPage]`
  citizen-facing route AND an OpenConnector-backed auth broker is wired

#### Scenario: Removing a claim once code confirms it is absent
- **GIVEN** a claim exists on any surface (product page, publiccode.yml, or
  docs) referencing a feature not found in `lib/` or `src/` after a direct
  code search
- **WHEN** that surface is next edited
- **THEN** the claim SHALL be removed or narrowed to what the code actually
  does, rather than left as aspirational marketing copy
