/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Shared test helpers for spec-coverage e2e tests.
 */

import type { Locator, Page } from '@playwright/test'
import { expect } from '@playwright/test'
import { APP } from '../app-path'

/**
 * Map a human nav label to its stable manifest menu id (the testid suffix
 * `cn-nav-entry-<id>`). The CnAppNav clusters entries into collapsible groups
 * (CasesGroup, RelationsGroup, ConfigGroup), and a group caption can share an
 * accessible name with one of its child entries — e.g. the "Cases" group and
 * the "Cases" (Zaken) entry both expose role=link name="Cases", which makes a
 * bare getByRole('link', { name: 'Cases' }) a strict-mode violation. Targeting
 * the entry by its testid is unambiguous and group-collapse independent.
 */
const NAV_ID: Record<string, string> = {
	Cases: 'Zaken',
	Tasks: 'Taken',
	Customers: 'Klanten',
	Employees: 'Medewerkers',
	Roles: 'Rollen',
	'Case types': 'Zaaktypen',
	Messages: 'Berichten',
	'Contact moments': 'Contactmomenten',
	Search: 'Search',
	Dashboard: 'Dashboard',
	'Features & roadmap': 'FeaturesRoadmapMenu',
	Settings: 'SettingsMenu',
	'Audit trail': 'AuditTrail',
}

/** The CnAppNav entry for a given menu id (testid `cn-nav-entry-<id>`). */
export function navEntry(page: Page, id: string): Locator {
	return page.getByTestId(`cn-nav-entry-${id}`)
}

/** The CnAppNav entry for a human label, resolved via NAV_ID. */
export function navEntryByLabel(page: Page, label: string): Locator {
	const id = NAV_ID[label]
	if (!id) throw new Error(`No nav id mapping for label "${label}"`)
	return navEntry(page, id)
}

/**
 * Navigate to `appRoute` through the in-app Vue router.
 *
 * The router runs in hash mode (src/router/index.js → mode: 'hash'), so the
 * in-app route is carried in the URL fragment. Several manifest pages
 * (besluiten, documenten, resultaten, auditTrail, features-roadmap, settings)
 * have NO matching server-side page route in `appinfo/routes.php`, but in hash
 * mode that does not matter: the SPA shell is served by the app root and the
 * fragment selects the client route. A path-form goto without a hash boots the
 * router at "/" (Dashboard) and the target page never mounts — so we deep-link
 * via the hash, exactly as a bookmark / a real user reaches these pages.
 *
 * `entryRoute` is accepted for backwards compatibility but is no longer used as
 * a separate server-side landing step; the hash deep-link reaches the target
 * directly.
 */
export async function spaNavigate(page: Page, appRoute: string, entryRoute = '/zaken'): Promise<void> {
	void entryRoute
	await page.goto(`${APP}/#${appRoute}`)
	await dismissSupportModal(page)
	// Confirm the shell mounted (the fragment route renders inside it).
	await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
}

/**
 * Dismiss the "Support Zaakafhandelapp" introductory modal if it appears.
 * The modal shows on first app visit per session. The Close button must be
 * scoped to the dialog to avoid strict-mode violations with other Close
 * buttons on the page (nav toggle, sidebar close, etc).
 */
export async function dismissSupportModal(page: Page): Promise<void> {
	const modal = page.getByRole('dialog', { name: 'Support Zaakafhandelapp' })
	if (await modal.isVisible({ timeout: 3_000 }).catch(() => false)) {
		// Scope the Close button to the modal dialog to avoid strict mode
		// violations with "Close navigation" and "Close sidebar" buttons.
		await modal.getByRole('button', { name: 'Close' }).click()
		await expect(modal).not.toBeVisible({ timeout: 5_000 })
	}
}
