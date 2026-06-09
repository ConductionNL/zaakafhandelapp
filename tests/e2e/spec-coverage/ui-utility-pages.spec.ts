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
 *   - BUG-1: features-roadmap / auditTrail / settings have no server-side
 *     page route, so a hard `page.goto` 404s (/settings 500s). They are
 *     reached client-side via `spaNavigate`, exactly as a user would.
 *   - BUG-2: the in-app Dashboard page body renders empty — none of the
 *     six manifest stats-block widgets mount. The dashboard-content
 *     assertion is therefore `test.fixme`'d until the widgets render; the
 *     remaining tests assert the surfaces that DO work.
 *   - BUG-3: hard-navigating to /settings throws a 500 (SettingsController
 *     calls the non-existent ObjectService::getRegisters()). Covered by an
 *     explicit guard test below.
 *
 * @see openspec/specs/ui-dashboard-widgets/spec.md
 * @see openspec/specs/app-configuration/spec.md
 */

import { test, expect } from '@playwright/test'
import { dismissSupportModal, spaNavigate } from './helpers'

const APP = '/apps/zaakafhandelapp'

test.describe('ui-utility-pages — dashboard, roadmap, audit-trail, settings', () => {

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#in-app-dashboard
	test('in-app dashboard — root route mounts the dashboard shell', async ({ page }) => {
		await page.goto(APP)
		await dismissSupportModal(page)
		// Shell mounts and the Dashboard page host is rendered into the DOM.
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		// BUG-2: the Dashboard page host is present but its body is empty
		// (zero-height / hidden) because no stats-block widget mounts, so we
		// assert it is attached + carries the Dashboard page id rather than
		// visible. Flip to toBeVisible once the widgets render.
		const dashHost = page.locator('[data-testid="cn-page"]')
		await expect(dashHost.first()).toBeAttached({ timeout: 10_000 })
		// The Dashboard nav item is present and active.
		await expect(page.getByRole('link', { name: 'Dashboard', exact: true }).first()).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#dashboard-stats-count
	// BUG-2: the six manifest stats-block widgets do not render on the
	// in-app dashboard — the page body is empty. Un-fixme once they mount.
	test.fixme('dashboard stats — the six manifest stats-block widgets render their titles', async ({ page }) => {
		await page.goto(APP)
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
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const appNav = page.locator('nav').filter({ has: page.getByRole('link', { name: 'Cases' }) })
		const roadmapLink = appNav.getByRole('link', { name: 'Features & roadmap' })
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
		// BUG-3: a hard goto to /settings 500s; reach it client-side.
		await spaNavigate(page, '/settings')
		await expect(page.locator('[data-testid="cn-settings-page"]')).toBeVisible({ timeout: 10_000 })
		// The settings form renders its section headings.
		await expect(page.getByText('Data storage', { exact: false }).first()).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-nav
	// BUG-3: clicking the in-app Settings nav button triggers a hard
	// navigation to /settings, which 500s (ObjectService::getRegisters()
	// undefined) instead of client-routing to the settings page. The button
	// is present and clickable but the destination is broken; assert the
	// button renders, and fixme the destination until the controller is
	// fixed. (The working client-routed path is covered by the test above.)
	test('settings nav — the Settings button is present in the left nav', async ({ page }) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const appNav = page.locator('nav').filter({ has: page.getByRole('link', { name: 'Cases' }) })
		const settingsBtn = appNav.getByRole('button', { name: 'Settings' })
		await expect(settingsBtn).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-nav-destination
	test.fixme('settings nav — clicking Settings opens the settings page (blocked by BUG-3)', async ({ page }) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const appNav = page.locator('nav').filter({ has: page.getByRole('link', { name: 'Cases' }) })
		await appNav.getByRole('button', { name: 'Settings' }).click()
		await expect(page.locator('[data-testid="cn-settings-page"]')).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/app-configuration/spec.md#settings-hardnav-500
	// BUG-3: a hard browser navigation / refresh to /settings throws a 500
	// because SettingsController::index calls the undefined OR method
	// ObjectService::getRegisters(). This guard test documents the broken
	// behaviour; flip the expectation to 200 once the controller is fixed.
	test('settings hard-nav — direct GET /settings currently returns 500 (BUG-3)', async ({ request }) => {
		const res = await request.get(`${APP}/settings`, { failOnStatusCode: false })
		// Documenting the live defect: a successful page would be 200.
		expect(res.status(), 'BUG-3: hard GET /settings should be 200 once SettingsController stops calling ObjectService::getRegisters()').toBe(500)
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
		await page.goto(APP)
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
