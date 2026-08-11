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

// NOTE: the `audit-trail` widget key is a library built-in (CnAuditTrailWidget).
// The former app-local adapter registry entry was removed in ADR-049 Phase-4 —
// CnDetailPage resolves manifest `type: "audit-trail"` widgets against the lib
// built-in, which self-fetches from the detail object-context merge.

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
		_note: 'Bespoke admin configuration form (src/views/settings/Settings.vue): a per-object-type source/register/schema mapping over 13 object types, with OpenRegister-installed detection and a reset path. No built-in widget models "write app config keys": object-table/card-grid/stats-block/chart read objects, and form-renderer writes an OBJECT against a schema, not app config. It is the same component the Nextcloud admin settings framework already mounts at /settings/admin/zaakafhandelapp via lib/Settings/ZaakAfhandelAppAdmin.php + src/settings.js — this registry entry exists ONLY to serve the second, in-app copy at the manifest page id=Settings. That duplicate surface is what gate-63 flags under ADR-079 D1 ("delete the in-app page"); resolving it removes this entry with it. Left in place pending that product decision — see the gate-63 report.',
	},

	// ── kind: "widget" (ZaakDetail sidebar tabs — ZGW-API relations) ────────
	// ADR-049 Phase-4 dissolution (updated for nextcloud-vue #89): the besluiten
	// tab was DISSOLVED to a built-in object-table sidebar-tab widget (see
	// src/manifest.json ZaakDetail config.sidebarTabs[id=besluiten].widgets) —
	// its former component entry is gone. The remaining SIX stay component STUBS
	// for ENDPOINT reasons (their ZGW list controllers drop the ?zaak filter, or
	// the relation is unverified), NOT a renderer gap. Per-tab endpoint reality
	// below.
	ZaakTakenTab: {
		kind: 'widget',
		component: ZaakTakenTab,
		_note: 'STUB — parent-zaak taken already render in the detail body (widget zaak-taken). api/taken forwards ?zaak=@objectId (OR). Not dissolved: no object-table sidebar-tab renderer.',
	},
	ZaakRollenTab: {
		kind: 'widget',
		component: ZaakRollenTab,
		_note: 'STUB — ZGW api/zrc/rollen exists but RollenController::index forwards no query (zaak filter dropped); OR api/objects/rollen?zaak=@objectId is reachable (rol.zaak=@objectId proven by the Roles summaryAggregate). Not dissolved: no object-table sidebar-tab renderer.',
	},
	ZaakDocumentenTab: {
		kind: 'widget',
		component: ZaakDocumentenTab,
		_note: 'STUB — ZGW api/zrc/zaakinformatieobjecten exists but index() forwards no query (zaak filter dropped); documents already render in the detail body (widget zaak-documenten). Not dissolved: no object-table sidebar-tab renderer.',
	},
	ZaakBerichtenTab: {
		kind: 'widget',
		component: ZaakBerichtenTab,
		_note: 'STUB — api/berichten (OR, forwards params) is reachable but bericht.zaak is UNVERIFIED (relation runs via klantcontact; schema seeded externally). Endpoint-not-built for a zaak filter; also no object-table sidebar-tab renderer.',
	},
	ZaakResultatenTab: {
		kind: 'widget',
		component: ZaakResultatenTab,
		_note: 'STUB — ZGW api/zrc/resultaten exists but index() forwards no query (zaak filter dropped); results already render in the detail body (widget zaak-resultaten). Not dissolved: no object-table sidebar-tab renderer.',
	},
	ZaakStatussenTab: {
		kind: 'widget',
		component: ZaakStatussenTab,
		_note: 'STUB — ZGW api/zrc/statussen exists but index() forwards no query (zaak filter dropped); status.zaak unverified. Endpoint-not-built for a zaak filter; also no object-table sidebar-tab renderer.',
	},
}
