# Tasks: add-spdx-license-headers

- [ ] 1.1 Add the EUPL-1.2 licence/copyright header docblock (matching the fleet house-style — `@copyright` Conduction B.V., `@license EUPL-1.2`, `SPDX-License-Identifier: EUPL-1.2`, `SPDX-FileCopyrightText`) to the top of every PHP file under `lib/` (53 files). Preserve any existing docblock content (append the licence tags; do not remove class/method docs). Do NOT alter code logic. Use the Edit/Write tools file-by-file (or the fleet's SPDX-insertion helper if one exists) — never a blind regex that could corrupt existing docblocks.
  - **spec_ref**: `specs/source-license-headers/spec.md#requirement-every-lib-php-file-carries-an-eupl-12-licence-and-copyright-header`
  - **acceptance_criteria**:
    - All 53 `lib/**/*.php` files contain `@license EUPL-1.2` + `@copyright` + `SPDX-License-Identifier: EUPL-1.2`
    - No code logic changed (diff is header-only)
- [ ] 1.2 Verify: `grep -rL 'SPDX-License-Identifier' lib --include='*.php'` returns nothing; the `spdx-headers` gate is green; `openspec validate add-spdx-license-headers --strict` is clean.
  - **spec_ref**: `specs/source-license-headers/spec.md#requirement-the-spdx-headers-gate-passes`
  - **acceptance_criteria**:
    - Zero lib PHP files missing the SPDX header; gate green
