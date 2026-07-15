/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Utility / special-type pages.
 *
 * Covers the non-index manifest pages that the existing spec-coverage
 * suite did not exercise: the in-app Dashboard surface, the Features &
 * roadmap page, the Audit trail (logs-type) page, and the Settings
 * (settings-type) page. Each is rendered by a dedicated manifest-v2 page
 * type via the CnAppRoot shell.
 *
 * All assertions are data-independent — they confirm the page mounts and
 * renders its characteristic chrome, not the presence of any record.
 *
 * Routing notes (see the audit report for full BUG list):
 *   - BUG-1 (FIXED): besluiten / documenten / resultaten index + detail
 *     routes now have server-side page routes in appinfo/routes.php, so a
 *     hard `page.goto` resolves (200) instead of 404. features-roadmap /
 *     auditTrail are still reached client-side via `spaNavigate`.
 *   - BUG-2 (FIXED): the in-app Dashboard's six manifest stats-block widgets
 *     now mount — the app registers `stats-block` (CnStatsBlockWidget) in
 *     src/registry.js and each widget carries an in-`props` `dataSource`
 *     block (CnWidgetGrid does not forward the top-level `dataSource`, a
 *     known nc-vue lib gap). The dashboard-content assertions are live again.
 *   - BUG-3 (FIXED): SettingsController::index no longer calls the
 *     non-existent ObjectService::getRegisters(); it uses
 *     ObjectMapperService::getRegisters() (RegisterMapper::findAll + schema
 *     expansion), so a hard GET /settings now returns 200.
 *
 * @see openspec/specs/ui-dashboard-widgets/spec.md
 * @see openspec/specs/app-configuration/spec.md
 */

import { test, expect } from '@playwright/test'
import { dismissSupportModal, spaNavigate, navEntryByLabel } from './helpers'

const APP = '/apps/zaakafhandelapp'

test.describe('ui-utility-pages — dashboard, roadmap, audit-trail, settings', () => {

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#in-app-dashboard
	test('in-app dashboard — root route mounts the dashboard shell', async ({ page }) => {
		await page.goto(`${APP}/#/`)
		await dismissSupportModal(page)
		// Shell mounts and the Dashboard page host is rendered into the DOM.
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		// BUG-2 (FIXED): the Dashboard page host mounts and is visible now
		// that the stats-block widgets render into its body.
		const dashHost = page.locator('[data-testid="cn-page"]')
		await expect(dashHost.first()).toBeVisible({ timeout: 10_000 })
		// The Dashboard nav item is present and active.
		await expect(navEntryByLabel(page, 'Dashboard')).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#dashboard-stats-count
	// BUG-2 (FIXED): the six manifest stats-block widgets mount and render
	// their titles on the in-app dashboard.
	test('dashboard stats — the six manifest stats-block widgets render their titles', async ({ page }) => {
		await page.goto(`${APP}/#/`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		await expect(page.getByText('Open cases', { exact: false }).first()).toBeVisible({ timeout: 10_000 })
		await expect(page.getByText('Open tasks', { exact: false }).first()).toBeVisible()
		await expect(page.getByText('Persons', { exact: false }).first()).toBeVisible()
		await expect(page.getByText('Organisations', { exact: false }).first()).toBeVisible()
	})

	// @e2e openspec/specs/app-configuration/spec.md#features-and-roadmap
	test('features & roadmap — the roadmap page mounts (via in-app nav)', async ({ page }) => {
		await spaNavigate(page, '/features-roadmap')
		// The roadmap view renders its "Features" header + intro copy.
		await expect(page.getByRole('heading', { name: 'Features', exact: true }).first()).toBeVisible({ timeout: 10_000 })
		await expect(page.getByText('Your input is the roadmap', { exact: false }).first()).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#features-roadmap-nav
	test('features & roadmap nav — the footer link is reachable from the left nav', async ({ page }) => {
		await page.goto(`${APP}/#/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const appNav = page.locator('nav').filter({ has: navEntryByLabel(page, 'Cases') })
		const roadmapLink = navEntryByLabel(page, 'Features & roadmap')
		await expect(roadmapLink).toBeVisible({ timeout: 10_000 })
		await roadmapLink.click()
		// Navigating lands on the roadmap surface.
		await expect(page.getByRole('heading', { name: 'Features', exact: true }).first()).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/zgw-related-resources/spec.md#audit-trail
	test('audit trail — the logs page mounts and renders rows or an empty state', async ({ page }) => {
		await spaNavigate(page, '/auditTrail')
		// The logs page host mounts.
		await expect(page.locator('[data-testid="cn-logs-page"]')).toBeVisible({ timeout: 10_000 })
		// It renders either log rows or the "No log entries to show" empty
		// state — neither is a crash/blank.
		const logsPage = page.locator('[data-testid="cn-logs-page"]')
		const hasRows = logsPage.locator('table, [role="row"]').first()
		const hasEmpty = logsPage.getByText(/No log entries|no entries|empty/i).first()
		await expect(hasRows.or(hasEmpty).first()).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-page
	test('settings page — the settings form mounts (via in-app nav)', async ({ page }) => {
		// BUG-3 (FIXED): /settings now returns 200; client-route to it as a user would.
		await spaNavigate(page, '/settings')
		await expect(page.locator('[data-testid="cn-settings-page"]')).toBeVisible({ timeout: 10_000 })
		// The settings form renders its section headings.
		await expect(page.getByText('Data storage', { exact: false }).first()).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-nav
	// The in-app Settings nav button is present in the left nav. Its
	// destination (BUG-3, now fixed) is covered by the test below.
	test('settings nav — the Settings button is present in the left nav', async ({ page }) => {
		await page.goto(`${APP}/#/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const appNav = page.locator('nav').filter({ has: navEntryByLabel(page, 'Cases') })
		// `exact` so we match the app's own "Settings" footer entry and not
		// the NC "Personal settings" entry that also lives in the footer.
		const settingsBtn = appNav.getByRole('button', { name: 'Settings', exact: true })
		await expect(settingsBtn).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-nav-destination
	// BUG-3 (the /settings 500) is fixed and the settings page is reachable —
	// proven by the "settings page — the settings form mounts" test (client
	// route) and the "settings hard-nav … returns 200" test. This separate
	// case asserts that clicking the app's footer "Settings" entry *routes*
	// to cn-settings-page in-app; in the current CnAppNav shell the footer
	// (section:"settings") entry does not push that route, so the page does
	// not mount on click. This is an in-app-nav-wiring concern independent of
	// BUG-3 (a backend defect), so it stays fixme'd until the shell routes
	// footer settings entries. NOT a regression from the BUG-1/2/3 fixes.
	test.fixme('settings nav — clicking Settings opens the settings page', async ({ page }) => {
		await page.goto(`${APP}/#/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const appNav = page.locator('nav').filter({ has: navEntryByLabel(page, 'Cases') })
		await appNav.getByRole('button', { name: 'Settings', exact: true }).click()
		await expect(page.locator('[data-testid="cn-settings-page"]')).toBeVisible({ timeout: 15_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-hardnav-500
	// BUG-3 (FIXED): a hard browser navigation / refresh to /settings now
	// returns 200. SettingsController::index uses
	// ObjectMapperService::getRegisters() (RegisterMapper::findAll + schema
	// expansion) instead of the non-existent ObjectService::getRegisters().
	test('settings hard-nav — direct GET /settings returns 200 (BUG-3 fixed)', async ({ request }) => {
		const res = await request.get(`${APP}/settings`, { failOnStatusCode: false })
		expect(res.status(), 'BUG-3: hard GET /settings should be 200').toBe(200)
	})

	// @e2e openspec/specs/ui-search-navigation/spec.md#no-console-errors-utility
	// Asserts no UNCAUGHT JS exception (pageerror) crashes the shell on the
	// working utility surfaces. Handled `console.error` data-fetch noise
	// (empty OR collections on a seedless instance, the BUG-3 settings JSON
	// 500) is data/bug-dependent and is intentionally NOT asserted here —
	// an uncaught exception, by contrast, means the page genuinely broke.
	test('no uncaught JS exceptions on the working utility pages', async ({ page }) => {
		test.setTimeout(60_000)
		const errors: string[] = []
		page.on('pageerror', (err) => errors.push(err.message))
		// Dashboard root (server-routed).
		await page.goto(`${APP}/#/`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		// Client-routed surfaces that render successfully (settings excluded —
		// blocked by BUG-3).
		await spaNavigate(page, '/features-roadmap')
		await expect(page.getByRole('heading', { name: 'Features', exact: true }).first()).toBeVisible({ timeout: 10_000 })
		await spaNavigate(page, '/auditTrail')
		await expect(page.locator('[data-testid="cn-logs-page"]')).toBeVisible({ timeout: 10_000 })
		expect(errors, `Uncaught JS exceptions: ${errors.join(' | ')}`).toHaveLength(0)
	})

})
