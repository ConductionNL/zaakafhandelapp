// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// V2 component registry for zaakafhandelapp.
// Replaces the v1 customComponents.js + formatters.js + cellWidgets.js trio.
//
// Each entry declares a `kind` field that CnAppRoot validates at mount:
//   - "page"          — a type:"custom" page component (page.component key)
//   - "widget"        — a sidebar-tab or settings-section component
//   - "modal"         — a cross-cutting modal opened via cnOpenModal()
//   - "form-field"    — a custom form field renderer
//   - "cell-renderer" — a custom cell renderer for index tables
//
// Recognised kinds: widget, modal, page, form-field, cell-renderer
//   (see RegistryKindError — REQ-MVR-002)
//
// Keep this file SHORT. Every entry is an escape hatch from the manifest's
// built-in types. Removing entries (by migrating to a built-in) is the
// right direction.
//
// Spec: openspec/changes/zaakafhandelapp-manifest-v1/design.md

// ── kind: "page" ───────────────────────────────────────────────────────────
// Pages referenced by manifest pages[].component (type: "custom")

import SearchView from './views/search/SearchIndex.vue'
import AuditTrailView from './views/audit/AuditTrailView.vue'

// ── kind: "widget" (dashboard stats-block) ──────────────────────────────────
// The dashboard manifest places `widgetKey: "stats-block"` cards in the "body"
// slot, but `stats-block` is NOT one of CnWidgetGrid's built-in widget keys
// (object-table, form-renderer, map-viewer, card-grid, data, metadata,
// integration). Without registering it here CnWidgetGrid logs "Unknown
// widgetKey" and skips every card, leaving the dashboard body empty.
// CnStatsBlockWidget reads the manifest `dataSource` block
// ({ register, schema, aggregate: "count" }) and renders CnStatsBlock.

import { CnStatsBlockWidget } from '@conduction/nextcloud-vue'

// ── kind: "widget" ─────────────────────────────────────────────────────────
// Settings section body component (type:"settings" sections[].component)

import SettingsForm from './views/settings/Settings.vue'

// ── kind: "widget" (sidebar tabs) ─────────────────────────────────────────
// ZaakDetail sidebar tabs for ZGW-API related objects.
// These tabs fetch data via ZGW-API controllers (not OR) and have no
// built-in widget analogue today.

import ZaakTakenTab from './components/tabs/ZaakTakenTab.vue'
import ZaakRollenTab from './components/tabs/ZaakRollenTab.vue'
import ZaakDocumentenTab from './components/tabs/ZaakDocumentenTab.vue'
import ZaakBesluitenTab from './components/tabs/ZaakBesluitenTab.vue'
import ZaakBerichtenTab from './components/tabs/ZaakBerichtenTab.vue'
import ZaakResultatenTab from './components/tabs/ZaakResultatenTab.vue'
import ZaakStatussenTab from './components/tabs/ZaakStatussenTab.vue'

export default {
	// ── kind: "page" ────────────────────────────────────────────────────────
	SearchView: {
		kind: 'page',
		component: SearchView,
		_note: 'Hybrid ZGW-API data path; searches across ZGW registers via multiple ZGW-API controllers, not OR; no current typed-page analogue',
	},
	AuditTrailView: {
		kind: 'page',
		component: AuditTrailView,
		_note: 'Hybrid ZGW-API data path; fetches audit events from ZGW-API audittrail endpoint, not OR; no current typed-page analogue',
	},

	// ── kind: "widget" (dashboard stats-block) ─────────────────────────────
	// Resolved by CnWidgetGrid via the manifest `widgetKey: "stats-block"`.
	// Reads the manifest `dataSource` block ({ register, schema, aggregate })
	// and renders a count KPI card. Without this entry the dashboard body is
	// empty (every stats-block card is skipped as an unknown widgetKey).
	'stats-block': {
		kind: 'widget',
		component: CnStatsBlockWidget,
		defaultSize: { w: 4, h: 2 },
		minSize: { w: 1, h: 1 },
		maxSize: { w: 12, h: 8 },
		allowedSlots: ['body'],
		propsSchema: {},
	},

	// ── kind: "widget" (settings section) ──────────────────────────────────
	SettingsForm: {
		kind: 'widget',
		component: SettingsForm,
	},

	// ── kind: "widget" (ZaakDetail sidebar tabs — ZGW-API relations) ────────
	ZaakTakenTab: {
		kind: 'widget',
		component: ZaakTakenTab,
		_note: 'Hybrid ZGW-API data path; fetches related taken via ZGW-API taak endpoint filtered by zaakUrl, not OR',
	},
	ZaakRollenTab: {
		kind: 'widget',
		component: ZaakRollenTab,
		_note: 'Hybrid ZGW-API data path; fetches related rollen via ZGW-API rol endpoint filtered by zaakUrl, not OR',
	},
	ZaakDocumentenTab: {
		kind: 'widget',
		component: ZaakDocumentenTab,
		_note: 'Hybrid ZGW-API data path; fetches related zaakinformatieobjecten via ZGW-API endpoint filtered by zaakUrl, not OR',
	},
	ZaakBesluitenTab: {
		kind: 'widget',
		component: ZaakBesluitenTab,
		_note: 'Hybrid ZGW-API data path; fetches related besluiten via ZGW-API besluit endpoint filtered by zaakUrl, not OR',
	},
	ZaakBerichtenTab: {
		kind: 'widget',
		component: ZaakBerichtenTab,
		_note: 'Hybrid ZGW-API data path; fetches related berichten via ZGW-API klantcontact endpoint filtered by zaakUrl, not OR',
	},
	ZaakResultatenTab: {
		kind: 'widget',
		component: ZaakResultatenTab,
		_note: 'Hybrid ZGW-API data path; fetches related resultaten via ZGW-API resultaat endpoint filtered by zaakUrl, not OR',
	},
	ZaakStatussenTab: {
		kind: 'widget',
		component: ZaakStatussenTab,
		_note: 'Hybrid ZGW-API data path; fetches related statussen via ZGW-API status endpoint filtered by zaakUrl, not OR',
	},
}
