---
kind: code
---

# Proposal: adopt-shared-menu-pipeline

## Summary

Zaakafhandelapp currently builds its effective menu from a single monolithic `src/manifest.json` (~1 129 lines) that hard-codes every navigation entry inline. ADR-044 "Menu architecture" mandates that all apps:

1. Deliver their manifest fragments through the ADR-037 modular `src/manifest.d/` pipeline (so fragments can be added, removed, or overridden without touching the root manifest).
2. Compose their runtime manifest via the shared `buildManifest(base, fragments, menuLayout)` helper from `@conduction/nextcloud-vue`.
3. Express navigation layout as data in a dedicated `src/menu-layout.json` file (`relocations`, `removals`, `settingsSection`).
4. Move configuration/admin leaves into the settings foldout via `settingsSection`, keeping the primary navigation free of admin-only items.

This change introduces all four steps for zaakafhandelapp without dropping a single pre-existing page route or reachable function.

## Motivation

- **ADR-044 compliance**: the shared `buildManifest` contract is now fleet-mandatory; monolithic manifests are non-compliant from the next release cycle.
- **ADR-037 compliance**: the fragment pipeline is a prerequisite for `buildManifest` adoption; zaakafhandelapp is the only remaining core app without it.
- **Clarity of intent**: the current `section: "settings"` entries (`Zaaktypen`, `Rollen`, `AuditTrail`, `SettingsMenu`) are spread inline through the menu array; `src/menu-layout.json` makes the promotion to the settings foldout explicit and auditable.
- **Maintainability**: future menu changes (new ZGW object types, ZGW-API deep-links) become fragment additions rather than inline edits to a 1 000-line file.
- **Zero regression guarantee**: the HARD INVARIANT encoded in the spec — every pre-existing menu entry stays reachable, every page stays routable — is testable against a deep-link route matrix.

## Affected Projects

- [x] Project: zaakafhandelapp
