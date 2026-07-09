---
kind: config
---

# Proposal: add-spdx-license-headers

## Why

The repository is licensed **EUPL-1.2** (the `LICENSE` file, `composer.json`
`"license": "EUPL-1.2"`, the README badge and §License, and `publiccode.yml` all agree),
but **not a single PHP file under `lib/` carries a licence/copyright header**: a
`grep -rl 'SPDX-License' lib/` returns 0 of 53 files. Every source file therefore ships
with no machine-readable provenance, which fails REUSE compliance, fails the fleet's
`spdx-headers` quality gate (which requires `@license` + `@copyright` PHPDoc on every
`lib/` PHP file), and — because the manifest still declares `<licence>agpl</licence>` as
the NC-app-store workaround (the app targets `min-version="28"`, below the NC 31 baseline
where `EUPL-1.2` became a valid `<licence>` xsd value) — leaves the file-level licence
completely unstated rather than merely inconsistent.

This is a pure compliance/readiness hygiene gap. It is the sole substantive finding for
zaakafhandelapp: the two 2026-06-11 HIGH flags are both **resolved at HEAD** — the
`info.xml` is no longer an OpenCatalogi copy-paste (correct `<id>`, name, summary), and
the ZRC/DRC controllers are implemented (their docblocks explicitly note the "former
not-implemented stub"; no `501` remains). The ZGW surface is comprehensive
(Zaken/Statussen/Rollen/Resultaten/Eigenschappen/Objecten/InformatieObjecten/Besluiten/
Types + Documenten + KCC controllers), and notifications correctly defer to OpenRegister's
ADR-031 dialect rather than an app-local NRC controller.

## What Changes

- Add a licence/copyright header to **every** PHP file under `lib/` (53 files): a PHPDoc
  block carrying `@copyright` (Conduction B.V.) and `@license EUPL-1.2 …`, plus the REUSE
  `SPDX-License-Identifier: EUPL-1.2` and `SPDX-FileCopyrightText` tags, in the fleet
  house-style (matching e.g. shillinq/openregister lib headers). No code logic changes.
- Leave `appinfo/info.xml` `<licence>agpl</licence>` as-is: at `min-version="28"` the
  App-Store xsd does not accept `EUPL-1.2`, so the `agpl` value is the deliberate,
  documented store-compatibility workaround (the source-of-truth licence is the `LICENSE`
  file + these new SPDX headers, both EUPL-1.2). A follow-up may raise the manifest to
  `EUPL-1.2` once the app's NC baseline moves to ≥ 31.

## Impact

- Affected: 53 `lib/**/*.php` files (header docblock only). No behavioural change.
- Brings the app into REUSE compliance and green on the `spdx-headers` gate; makes the
  per-file licence explicit and consistent with the repository licence.
