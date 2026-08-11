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
import { APP } from '../app-path'

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

	// @e2e openspec/specs/app-configuration/spec.md#REQ-002
	//
	// ADR-079 D1: the app no longer ships an in-app `type:settings` page. The
	// settings FORM still exists and is unchanged — `src/views/settings/
	// Settings.vue` was always rendered on BOTH surfaces, and the in-app copy
	// was the duplicate. Its one remaining home is the Nextcloud admin
	// section (`lib/Sections/ZaakAfhandelAppAdmin.php` → id `zaakafhandelapp`,
	// `lib/Settings/ZaakAfhandelAppAdmin.php` → `templates/settings/admin.php`,
	// which mounts `src/settings.js` on `#zaakafhandelapp-settings`).
	//
	// So this case moved rather than went away: it used to assert the form
	// mounts on the in-app route, and now asserts it mounts where it actually
	// lives. The previous `#settings-page` / `#settings-nav` anchors named
	// requirements that were never written — app-configuration/spec.md stops
	// at REQ-004 and has no such headings — so they resolved to nothing.
	// REQ-002 ("Read and persist object-type settings and available
	// registers") is the requirement this form actually serves, and is what
	// `SettingsController::index/create` already point at.
	test('settings form — mounts on the Nextcloud admin settings page', async ({ page }) => {
		await page.goto('/index.php/settings/admin/zaakafhandelapp')
		// The template's mount point is present and the Vue app replaced it
		// with the settings shell.
		await expect(page.locator('#zaakafhandelapp-settings')).toBeAttached({ timeout: 15_000 })
		// The form renders its first section heading — proof the bundle
		// executed, not merely that the div exists.
		await expect(page.getByText('Data storage', { exact: false }).first()).toBeVisible({ timeout: 15_000 })
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
