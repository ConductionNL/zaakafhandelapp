---
kind: code
---

# Proposal: consolidate-audit-trail-modal

## Why

Zaakafhandelapp ships **five** near-identical "view one audit-trail entry" modal
components, copy-pasted rather than shared:

- `src/modals/zaken/ViewZaakAuditTrail.vue` (148 lines)
- `src/modals/taken/ViewTaakAuditTrail.vue` (97 lines)
- `src/modals/berichten/ViewBerichtAuditTrail.vue` (97 lines)
- `src/modals/klanten/ViewKlantAuditTrail.vue` (97 lines)
- `src/modals/medewerkers/ViewKlantAuditTrail.vue` (97 lines)

A line-for-line diff of the zaken and taken variants (`diff src/modals/zaken/ViewZaakAuditTrail.vue src/modals/taken/ViewTaakAuditTrail.vue`)
shows the template, the `formatValue()` helper, and the styles are identical;
the only differences are which store module is imported (`zaakStore` vs.
`takenStore`/`berichtenStore`/`klantenStore`) and the component's registered
`name`. This is the exact bespoke-duplication pattern ADR-017 (component
composition) exists to prevent.

Three further, more serious problems, each independently verified against
the code:

1. **Dead code.** `src/modals/medewerkers/ViewKlantAuditTrail.vue` is never
   imported anywhere. `src/modals/Modals.vue` wires up
   `ViewZaakAuditTrail`, `ViewKlantAuditTrail`, `ViewBerichtAuditTrail`, and
   `ViewTaakAuditTrail` (`src/modals/Modals.vue:59-99`) but has **no**
   medewerkers-audit-trail entry — the medewerkers section only wires
   `EditMedewerker` (`src/modals/Modals.vue:39-40,69,104`). A repo-wide grep
   for `modals/medewerkers/ViewKlantAuditTrail` and for the modal key it
   would need (`viewMedewerkerAuditTrail`) returns zero hits. It is also
   misnamed — its internal component `name` is `'ViewKlantAuditTrail'`
   (`src/modals/medewerkers/ViewKlantAuditTrail.vue:50`), a copy-paste
   artifact from the klanten variant it was cloned from.

2. **Hardcoded color (ADR-004 violation).** All five files hardcode
   `border-bottom: 1px solid #ccc;` in their `<style scoped>` block (e.g.
   `src/modals/zaken/ViewZaakAuditTrail.vue:112`,
   `src/modals/taken/ViewTaakAuditTrail.vue:86`,
   `src/modals/berichten/ViewBerichtAuditTrail.vue:86`,
   `src/modals/klanten/ViewKlantAuditTrail.vue:86`,
   `src/modals/medewerkers/ViewKlantAuditTrail.vue:86`) instead of an NC CSS
   variable (e.g. `var(--color-border)`), even though the same file already
   uses `var(--color-main-background)` two rules below
   (`src/modals/zaken/ViewZaakAuditTrail.vue:136`), so the app's own house
   style is inconsistent within one file, repeated five times.

3. **Superseded by the app's own declarative widget (ADR-036) and
   mis-tagged (`@spec` traceability).** `zaakafhandelapp` already ships a
   canonical, generic audit-trail surface —
   `src/components/widgets/AuditTrailWidget.vue`, registered in
   `src/registry.js:96-104` under the `audit-trail` widget key, which
   self-fetches from "the detail object context (register/schema/objectId)"
   per its own comment (`src/registry.js:94-95`) and is reusable across every
   entity's detail page. The five legacy modals duplicate that same "show one
   audit-trail entry" job through a parallel, non-declarative, per-entity
   path that predates the widget. Separately, all five `closeDialog()` /
   `formatValue()` methods carry `@spec
   openspec/specs/ui-modals/spec.md#REQ-004` (e.g.
   `src/modals/zaken/ViewZaakAuditTrail.vue:83,90`), but `ui-modals` REQ-004
   is "Surface success and error feedback"
   (`openspec/specs/ui-modals/spec.md:51-54`) — an unrelated requirement.
   There is no `ui-modals` requirement that actually describes viewing one
   audit-trail entry, so the gate-16 spec-coverage tag is currently pointing
   at the wrong requirement for all five files.

## What Changes

- Add a single shared `src/modals/shared/AuditTrailEntryModal.vue` that takes
  `auditTrail` (object) and `labelId` (string) as props and emits `close`;
  it owns the template, the `formatValue()` helper, and the styles
  (`var(--color-border)` in place of the hardcoded `#ccc`) exactly once.
- Re-wire `src/modals/Modals.vue`'s four live audit-trail entries
  (`viewZaakAuditTrail`, `viewKlantAuditTrail`, `viewBerichtAuditTrail`,
  `viewTaakAuditTrail`) to render `AuditTrailEntryModal` directly, passing
  each entity's `auditTrailItem` from its own store and calling that store's
  `setAuditTrailItem(null)` on close (preserving each entity's existing
  close/store-reset behaviour).
- **BREAKING (internal only, no user-facing regression):** delete
  `src/modals/zaken/ViewZaakAuditTrail.vue`,
  `src/modals/taken/ViewTaakAuditTrail.vue`,
  `src/modals/berichten/ViewBerichtAuditTrail.vue`, and
  `src/modals/klanten/ViewKlantAuditTrail.vue` — superseded by the shared
  component. No template/behaviour change is visible to the user.
- Delete the dead `src/modals/medewerkers/ViewKlantAuditTrail.vue` outright
  (confirmed unreferenced; no migration needed).
- Add `ui-modals` REQ-006 "View a resource's audit-trail entry" (the
  requirement the five files were actually implementing, now correctly
  anchored) and re-point the shared component's `@spec` tags at it instead
  of the mismatched REQ-004.

## Impact

- **Affected specs**: `ui-modals` (new REQ-006).
- **Affected code**: `src/modals/shared/AuditTrailEntryModal.vue` (new),
  `src/modals/Modals.vue`, deletion of 4 duplicate modal files + 1 dead file
  (~536 lines net removed, ~110 lines added).
- No API, route, or schema changes. No user-visible behaviour change —
  same modal content, same open/close triggers, same per-entity store
  wiring — this is a pure de-duplication + dead-code removal + ADR-004 fix.
