# source-license-headers Specification

## Purpose
TBD - created by archiving change add-spdx-license-headers. Update Purpose after archive.

Both scenarios below are static REUSE checks over `lib/**/*.php`, not runtime UI
flows, so each carries a per-scenario `@e2e exclude` naming what does enforce it.

They are written per scenario, not once for the spec, because a whole-spec
`@e2e exclude` silently covers every scenario added later too. The previous
annotation here was a single line at the END of the file: a whole-spec exclusion
is only read BEFORE the first `###` heading, so that line was not a whole-spec
exclusion at all — it landed inside the last scenario's block and excluded only
that one. gate-19 duly reported the OTHER scenario as uncovered, which is how it
was found.

## Requirements
### Requirement: Every lib PHP file carries an EUPL-1.2 licence and copyright header

Every PHP file under `lib/` MUST carry a licence/copyright header in its top docblock
declaring the repository licence (EUPL-1.2): a `@copyright` tag (Conduction B.V.), a
`@license EUPL-1.2` tag, and the REUSE `SPDX-License-Identifier: EUPL-1.2` and
`SPDX-FileCopyrightText` tags. The declared per-file licence MUST match the `LICENSE`
file, `composer.json`, `publiccode.yml`, and the README (all EUPL-1.2). No `lib/` PHP
file may ship with an absent or contradictory licence header.

#### Scenario: A lib source file declares its licence

- **WHEN** any `lib/**/*.php` file is inspected
- **THEN** its top docblock MUST contain `@license EUPL-1.2`, `@copyright`, and `SPDX-License-Identifier: EUPL-1.2`
- **AND** the value MUST match the repository `LICENSE` (EUPL-1.2)

@e2e exclude header presence in lib/**/*.php is a static REUSE check with no browser surface; enforced on every push by the spdx-headers gate (gate-1), which scans lib/ and reports PASS.

#### Scenario: The spdx-headers gate passes

- **WHEN** the `spdx-headers` quality gate scans `lib/`
- **THEN** it MUST report zero files missing `@license`/`@copyright`
- **AND** the count of `lib/**/*.php` files with `SPDX-License-Identifier` MUST equal the total count of such files

@e2e exclude this scenario IS the spdx-headers gate (gate-1) — it runs on every push and reports PASS; a Playwright spec cannot observe a quality gate.

