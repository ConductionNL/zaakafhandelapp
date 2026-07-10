## 1. Shared component

- [ ] 1.1 Create `src/modals/shared/AuditTrailEntryModal.vue`, ported from `src/modals/zaken/ViewZaakAuditTrail.vue` (the most complete of the five), with:
  - Props: `auditTrail` (Object, required), `labelId` (String, default `'View Audit Trail modal'`).
  - Emits: `close`.
  - The `formatValue()` helper unchanged.
  - `border-bottom: 1px solid var(--color-border);` in place of the hardcoded `#ccc` (all five source files use `#ccc` at `src/modals/zaken/ViewZaakAuditTrail.vue:112`, `src/modals/taken/ViewTaakAuditTrail.vue:86`, `src/modals/berichten/ViewBerichtAuditTrail.vue:86`, `src/modals/klanten/ViewKlantAuditTrail.vue:86`, `src/modals/medewerkers/ViewKlantAuditTrail.vue:86`).
- [ ] 1.2 Add `@spec openspec/specs/ui-modals/spec.md#REQ-006` to the component's script block (replacing the mismatched `#REQ-004` tags carried by the five files being replaced).

## 2. Re-wire Modals.vue

- [ ] 2.1 In `src/modals/Modals.vue`, replace the four `<ViewZaakAuditTrail>` / `<ViewKlantAuditTrail>` / `<ViewBerichtAuditTrail>` / `<ViewTaakAuditTrail>` entries (lines 9, 26, 32, 35) with four `<AuditTrailEntryModal>` invocations, one per store, e.g.:
  ```html
  <AuditTrailEntryModal
      v-if="navigationStore.modal === 'viewZaakAuditTrail'"
      :audit-trail="zaakStore.auditTrailItem || {}"
      label-id="View Zaak Audit Trail modal"
      @close="navigationStore.setModal(null); zaakStore.setAuditTrailItem(null)" />
  ```
  repeated for `takenStore` (`viewTaakAuditTrail`), `berichtenStore` (`viewBerichtAuditTrail`), `klantenStore` (`viewKlantAuditTrail`) — matching each store's existing `auditTrailItem` / `setAuditTrailItem` shape (`src/store/modules/zaken.ts:18,37`, `src/store/modules/taak.js:13,40`, `src/store/modules/berichten.js:12,31`, `src/store/modules/klanten.js:13,39`).
- [ ] 2.2 Update the `import` list and `components` map in `src/modals/Modals.vue` (currently `src/modals/Modals.vue:59-64,94-99`): remove the four retired imports, add `AuditTrailEntryModal` from `./shared/AuditTrailEntryModal.vue`, and add whichever of `zaakStore`/`takenStore`/`berichtenStore`/`klantenStore` isn't already in scope via the `<script setup>` store import (`src/modals/Modals.vue:2`).

## 3. Delete superseded + dead files

- [ ] 3.1 Delete `src/modals/zaken/ViewZaakAuditTrail.vue`, `src/modals/taken/ViewTaakAuditTrail.vue`, `src/modals/berichten/ViewBerichtAuditTrail.vue`, `src/modals/klanten/ViewKlantAuditTrail.vue`.
- [ ] 3.2 Delete `src/modals/medewerkers/ViewKlantAuditTrail.vue` outright — confirmed unreferenced (no import in `src/modals/Modals.vue`, no `viewMedewerkerAuditTrail`/`viewKlantAuditTrail` modal key set from any medewerkers view; repo-wide grep for the file path and for `ViewKlantAuditTrail` returns only the file itself).
- [ ] 3.3 Repo-wide grep for `ViewZaakAuditTrail|ViewTaakAuditTrail|ViewBerichtAuditTrail|ViewKlantAuditTrail` after deletion to confirm zero remaining references outside `Modals.vue`'s new wiring.

## 4. Spec + traceability

- [ ] 4.1 Add `ui-modals` REQ-006 "View a resource's audit-trail entry" (delta spec in this change) — the requirement the five deleted files were actually implementing under the wrong `@spec` anchor (`#REQ-004`, which is "Surface success and error feedback").
- [ ] 4.2 Run `openspec validate consolidate-audit-trail-modal --strict` and resolve any errors.
- [ ] 4.3 Manual verify: open the audit-trail modal from a zaak, klant, taak, and bericht detail view; confirm identical content/behaviour to before the change, and that the modal's divider renders using the current NC theme's border color (not a fixed grey) in both light and dark theme.
