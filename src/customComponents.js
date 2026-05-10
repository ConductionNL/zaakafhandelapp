// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Custom-component registry for zaakafhandelapp's manifest-driven app
// shell. Mirrors the decidesk reference (src/customComponents.js).
//
// Every entry here is the "escape hatch" — pages or sidebar tabs that
// don't fit one of the manifest's built-in types/widgets. Keep this
// file SHORT. Adding entries should require explicit justification in
// the design doc; deleting them is the right direction.
//
// Resolution order at runtime:
//   1. Built-in page types          (CnIndexPage, CnDetailPage, …)
//   2. Built-in widget types        (version-info, register-mapping, …)
//   3. customComponents (this file) ← consumer-injected components
//
// See:
//   - openspec/changes/zaakafhandelapp-manifest-v1/design.md
//   - @conduction/nextcloud-vue → docs/migrating-to-manifest.md

// --- Custom-fallback page components (referenced by `pages[].component`) ---
import SearchView from './views/search/SearchIndex.vue'
import AuditTrailView from './views/audit/AuditTrailView.vue'

// --- Settings section component (referenced by settings page sections[]). ---
// `manifest-settings-rich-sections` lets each section declare a custom
// component that renders the entire section body. We wrap the legacy
// settings page so the migration happens incrementally; future rounds
// can split this into per-section widgets.
import SettingsForm from './views/settings/Settings.vue'

// --- Detail-tab custom components for ZaakDetail ---
// Each tab references a thin stub today (`<CnNoteCard>` placeholder
// pointing at the existing modal-based UI). Implementations land in
// a follow-up sibling change once runtime regression confirms the
// dispatcher.
import ZaakTakenTab from './components/tabs/ZaakTakenTab.vue'
import ZaakRollenTab from './components/tabs/ZaakRollenTab.vue'
import ZaakDocumentenTab from './components/tabs/ZaakDocumentenTab.vue'
import ZaakBesluitenTab from './components/tabs/ZaakBesluitenTab.vue'
import ZaakBerichtenTab from './components/tabs/ZaakBerichtenTab.vue'
import ZaakResultatenTab from './components/tabs/ZaakResultatenTab.vue'
import ZaakStatussenTab from './components/tabs/ZaakStatussenTab.vue'

export default {
	// --- Genuine exception: multi-store search; no abstract analogue. ---
	SearchView,
	// --- Migration cost: placeholder navigation entry. ---
	AuditTrailView,
	// --- Settings wrapper (lib gap). ---
	SettingsForm,

	// --- ZaakDetail sidebar tabs (cross-schema relations). ---
	ZaakTakenTab,
	ZaakRollenTab,
	ZaakDocumentenTab,
	ZaakBesluitenTab,
	ZaakBerichtenTab,
	ZaakResultatenTab,
	ZaakStatussenTab,
}
