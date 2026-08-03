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

/**
 * Open the index page's Search/Columns sidebar and wait for it to render.
 *
 * WHY THIS IS A STEP AND NOT AN ASSUMPTION
 * ----------------------------------------
 * `CnIndexPage` used to mount with `sidebarOpen: true`. nc-vue commit 9c0475f6
 * ("fix(CnIndexPage): index sidebar closed by default with toggle", released in
 * v1.0.0-beta.119) flipped that default to `false`, and this suite — authored
 * before that change and not re-run on a clean instance since — assumed the old
 * behaviour.
 *
 * That matters for more than the sidebar itself. `CnIndexPage`'s `showTitle`
 * defaults to false, and its own docs say "the title is shown in the sidebar
 * header instead" — so with the sidebar closed the index page renders NO
 * heading at all, and the Search / Columns tabs do not exist either. Eight
 * "index renders list chrome" tests and every "search input is accessible"
 * test were failing on that single cause.
 *
 * Opening it here is the real user action the current UI requires, performed
 * through the same control a user clicks (`aria-label="Search and columns"`,
 * `CnActionsBar`), rather than an assertion relaxed to match a closed sidebar.
 */
export async function openIndexSidebar(page: Page): Promise<void> {
	const toggle = page.getByRole('button', { name: 'Search and columns' }).first()
	await expect(toggle).toBeVisible({ timeout: 15_000 })
	// The toggle reflects state via aria-pressed, so this stays idempotent —
	// calling it twice must not close a sidebar somebody else opened.
	if ((await toggle.getAttribute('aria-pressed')) !== 'true') {
		await toggle.click()
	}
	await expect(page.getByRole('tab', { name: 'Search' }).first()).toBeVisible({ timeout: 10_000 })
}

/**
 * Expand every collapsible section of the app's left nav.
 *
 * `CnAppNav` renders the manifest menu as it is declared in src/manifest.json:
 *
 *   - `CasesGroup` (Zaken / Taken / Search) and `RelationsGroup` (Klanten /
 *     Medewerkers / Contactmomenten / Berichten) are collapsible parents. Only
 *     the group containing the ACTIVE route is expanded, so from `/zaken` the
 *     four Relations children are rendered but hidden — `toBeVisible()` on them
 *     reports `hidden`, not "not found".
 *   - `Zaaktypen`, `Rollen`, `AuditTrail` and `SettingsMenu` declare
 *     `section: "settings"`, which puts them inside NC's settings foldout at the
 *     bottom of the nav. That foldout is closed on load.
 *
 * A user reaches those entries by opening the group / foldout, so the suite
 * does the same. (This also corrects an inconsistency in ui-nav-navigation,
 * which listed `Rollen` as a main-section entry while the manifest declares it
 * under `section: "settings"`.)
 */
export async function expandNav(page: Page): Promise<void> {
	const nav = page.locator('[data-testid="cn-nav"]')
	await expect(nav).toBeVisible({ timeout: 15_000 })

	// Collapsed NcAppNavigationItem parents expose an "Open menu" toggle;
	// expanded ones expose "Collapse menu". Loop until none are left rather
	// than assuming how many groups the manifest declares.
	for (let i = 0; i < 8; i++) {
		const toggles = nav.getByRole('button', { name: 'Open menu' })
		if (await toggles.count() === 0) {
			break
		}
		const before = await toggles.count()
		await toggles.first().click()
		// The clicked toggle relabels itself to "Collapse menu", so the match
		// count drops. Wait for that rather than a fixed sleep. If it does not
		// drop, stop looping and let the caller's own assertion report the
		// real problem instead of failing inside this helper.
		const dropped = await expect
			.poll(async () => toggles.count(), { timeout: 5_000 })
			.toBeLessThan(before)
			.then(() => true, () => false)
		if (!dropped) {
			break
		}
	}

	// The settings foldout. Probe a known settings-section entry rather than
	// trusting an aria attribute on the foldout button: if the entry is already
	// visible there is nothing to open, and if it is not, the foldout is shut.
	const probe = nav.locator('[data-testid="cn-nav-entry-SettingsMenu"]')
	if (!(await probe.isVisible({ timeout: 1_000 }).catch(() => false))) {
		const foldout = nav.locator('[data-testid="cn-nav-settings"] button').first()
		if (await foldout.isVisible({ timeout: 2_000 }).catch(() => false)) {
			await foldout.click()
			await expect(probe).toBeVisible({ timeout: 5_000 }).catch(() => undefined)
		}
	}
}
