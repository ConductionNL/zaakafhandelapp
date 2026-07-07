/**
 * SPDX-License-Identifier: EUPL-1.2
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 *
 * Shared config for zaakafhandelapp's compact dashboard list widgets
 * (zaken, open zaken, taken, personen, organisaties, contactmomenten).
 * Each widget self-fetches and shapes its rows to `{ id, mainText, subText }`,
 * then renders the universal `<CnDataTable>` headerless with these columns —
 * a bold name and a muted, right-aligned trailing status — matching the
 * ADR-049 compact list look shared with procest and scholiq.
 */

import { registerIcons } from '@conduction/nextcloud-vue'

import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import EmailOutline from 'vue-material-design-icons/EmailOutline.vue'
import FaceAgent from 'vue-material-design-icons/FaceAgent.vue'
import MailboxOpenOutline from 'vue-material-design-icons/MailboxOpenOutline.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import Phone from 'vue-material-design-icons/Phone.vue'

// The widget entry points (src/*Widget.js) mount each widget standalone on the
// Nextcloud Dashboard, bypassing src/main.js — so the MDI icons used by the
// widgets' leading `rowIcon` column are registered here, as a module
// side-effect shared by all six widgets. CnIcon resolves them by PascalCase
// name from this registry; icons render as theme-aware components, replacing
// the old per-theme avatar SVG URLs.
registerIcons({
	AccountOutline,
	BriefcaseAccountOutline,
	CalendarMonthOutline,
	ChatOutline,
	EmailOutline,
	FaceAgent,
	MailboxOpenOutline,
	OfficeBuildingOutline,
	Phone,
})

/**
 * Columns for a headerless name + trailing-status list. `mainText` and
 * `subText` are the keys produced by each widget's item shaping; the
 * `cn-cell--*` utilities live in nextcloud-vue's table.css.
 *
 * @type {Array<{key: string, cellClass: string}>}
 */
export const WIDGET_COLUMNS = [
	{ key: 'mainText', cellClass: 'cn-cell--strong' },
	{ key: 'subText', cellClass: 'cn-cell--muted cn-cell--end' },
]

/**
 * Leading row icon for a contactmoment row, varying per kanaal — the CnIcon
 * registry name for the channel (telefoon/email/brief/balie), falling back to
 * the generic chat icon. Mirrors the per-kanaal avatar icons of the previous
 * NcDashboardWidget rendering.
 *
 * @param {{kanaal?: string}} row The shaped contactmoment row.
 * @return {string} PascalCase MDI icon name registered with CnIcon.
 */
export function contactMomentIcon(row) {
	switch (row?.kanaal) {
	case 'telefoon':
		return 'Phone'
	case 'email':
		return 'EmailOutline'
	case 'brief':
		return 'MailboxOpenOutline'
	case 'balie':
		return 'FaceAgent'
	default:
		return 'ChatOutline'
	}
}
