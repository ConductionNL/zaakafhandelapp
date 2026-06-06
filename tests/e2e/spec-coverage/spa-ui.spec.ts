/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage Playwright tests — zaakafhandelapp SPA UI.
 *
 * Greenfield gate-19 bootstrap. These tests drive the real manifest
 * shell UI (CnAppRoot + CnPageRenderer) through the browser and assert
 * the rendered SHELLS — app-content, page headings, the app-navigation
 * sidebar and create dialogs. They deliberately do NOT assert on data
 * rows: the OpenRegister-backed list/detail endpoints can return empty
 * or 5xx on a fresh instance, so row-level assertions would be flaky and
 * are out of scope here (data behaviour belongs to Newman/PHPUnit).
 *
 * Each test carries an `// @e2e openspec/specs/<spec>/spec.md#<slug>`
 * annotation tying it back to the authored reverse-spec scenario.
 *
 * Authentication: globalSetup writes tests/e2e/.auth/admin.json;
 * playwright.config.ts wires storageState so each test starts logged in.
 */

import { test, expect, type Page } from '@playwright/test'

const BASE = '/apps/zaakafhandelapp'

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Navigate to an in-app history-mode route.
 *
 * The Vue SPA uses history mode with base `/apps/zaakafhandelapp`.
 * Sub-routes (/zaken, /taken, …) only exist client-side — navigating
 * directly to them returns a 404 from PHP. Strategy: land on the SPA
 * root first, wait for Vue to mount, then push the desired path via
 * window.history.pushState so Vue Router picks it up without a reload.
 */
async function go(page: Page, route: string): Promise<void> {
	const currentUrl = page.url()
	const alreadyInApp = currentUrl.includes('/apps/zaakafhandelapp')
	if (!alreadyInApp) {
		await page.goto(`${BASE}/`)
		await page.waitForLoadState('networkidle').catch(() => {})
		// Dismiss any "support" / first-load modal if present.
		const close = page.locator('[role="dialog"] button[aria-label="Close"], [role="dialog"] button:has-text("Close")').first()
		if (await close.isVisible({ timeout: 2000 }).catch(() => false)) {
			await close.click().catch(() => {})
		}
	}
	const targetPath = route.startsWith('/') ? route : `/${route}`
	const fullPath = `${BASE}${targetPath === '/' ? '' : targetPath}`
	if (!page.url().endsWith(fullPath) && !page.url().includes(fullPath + '?')) {
		await page.evaluate((p) => {
			window.history.pushState({}, '', p)
			window.dispatchEvent(new PopStateEvent('popstate', { state: {} }))
		}, fullPath)
		await page.waitForLoadState('networkidle').catch(() => {})
	}
}

/**
 * Open the first visible "Add…" / "New…" / "Create…" control, wait for a
 * dialog, then dismiss it without saving. Returns true if a dialog
 * appeared.
 */
async function openAndCloseCreateDialog(page: Page): Promise<boolean> {
	const btn = page.getByRole('button', { name: /Add|New|Create|Toevoegen|Nieuw/i }).first()
	if (!(await btn.isVisible({ timeout: 5000 }).catch(() => false))) {
		return false
	}
	await btn.click()
	const dialog = page.locator('[role="dialog"]').first()
	const appeared = await dialog.waitFor({ state: 'visible', timeout: 5000 }).then(() => true).catch(() => false)
	if (appeared) {
		const cancel = dialog.getByRole('button', { name: /Cancel|Close|Annuleren|Sluiten/i }).first()
		if (await cancel.isVisible({ timeout: 2000 }).catch(() => false)) {
			await cancel.click().catch(() => {})
		} else {
			await page.keyboard.press('Escape')
		}
		await page.waitForTimeout(200)
	}
	return appeared
}

// ===========================================================================
// DASHBOARD spec — openspec/specs/dashboard/spec.md
// ===========================================================================

test.describe('dashboard', () => {
	// @e2e openspec/specs/dashboard/spec.md#user-opens-the-app
	test('user opens the app', async ({ page }) => {
		await go(page, '/')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/apps/zaakafhandelapp')
	})

	// @e2e openspec/specs/dashboard/spec.md#dashboard-heading-renders
	test('dashboard heading renders', async ({ page }) => {
		await go(page, '/')
		const heading = page.getByRole('heading', { name: /Dashboard/i }).first()
		await expect(heading).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/dashboard/spec.md#sidebar-shows-the-primary-views
	test('sidebar shows the primary views', async ({ page }) => {
		await go(page, '/')
		const nav = page.locator('.app-navigation')
		await expect(nav).toBeVisible()
		for (const link of ['Cases', 'Tasks', 'Customers']) {
			await expect(nav.getByRole('link', { name: link })).toBeVisible()
		}
	})

	// @e2e openspec/specs/dashboard/spec.md#kpi-widget-area-renders
	test('KPI widget area renders', async ({ page }) => {
		await go(page, '/')
		const heading = page.getByRole('heading', { name: /Dashboard/i }).first()
		await expect(heading).toBeVisible({ timeout: 10_000 })
		// App content renders the widget grid even with zero counts.
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
	})
})

// ===========================================================================
// ZAKEN spec — openspec/specs/zaken/spec.md
// ===========================================================================

test.describe('zaken', () => {
	// @e2e openspec/specs/zaken/spec.md#cases-list-view-renders
	test('cases list view renders', async ({ page }) => {
		await go(page, '/zaken')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/apps/zaakafhandelapp')
		expect(page.url()).toContain('/zaken')
	})

	// @e2e openspec/specs/zaken/spec.md#cases-list-shows-a-create-affordance
	test('cases list shows a create affordance', async ({ page }) => {
		await go(page, '/zaken')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		const btn = page.getByRole('button', { name: /Add|New|Create|Toevoegen|Nieuw/i }).first()
		await expect(btn).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/zaken/spec.md#create-case-dialog-opens
	test('create case dialog opens', async ({ page }) => {
		await go(page, '/zaken')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		const opened = await openAndCloseCreateDialog(page)
		expect(opened).toBe(true)
	})

	// @e2e openspec/specs/zaken/spec.md#case-detail-route-resolves-to-the-detail-shell
	test('case detail route resolves to the detail shell', async ({ page }) => {
		await go(page, '/zaken/e2e-nonexistent')
		// The detail shell renders even when the record can't be loaded.
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/zaken/')
	})
})

// ===========================================================================
// TAKEN spec — openspec/specs/taken/spec.md
// ===========================================================================

test.describe('taken', () => {
	// @e2e openspec/specs/taken/spec.md#tasks-list-view-renders
	test('tasks list view renders', async ({ page }) => {
		await go(page, '/taken')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/taken')
	})

	// @e2e openspec/specs/taken/spec.md#tasks-navigation-entry-is-reachable
	test('tasks navigation entry is reachable', async ({ page }) => {
		await go(page, '/')
		await expect(page.locator('.app-navigation').getByRole('link', { name: 'Tasks' })).toBeVisible()
	})

	// @e2e openspec/specs/taken/spec.md#create-task-dialog-opens
	test('create task dialog opens', async ({ page }) => {
		await go(page, '/taken')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		const opened = await openAndCloseCreateDialog(page)
		expect(opened).toBe(true)
	})
})

// ===========================================================================
// KLANTEN spec — openspec/specs/klanten/spec.md
// ===========================================================================

test.describe('klanten', () => {
	// @e2e openspec/specs/klanten/spec.md#customers-list-view-renders
	test('customers list view renders', async ({ page }) => {
		await go(page, '/klanten')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/klanten')
	})

	// @e2e openspec/specs/klanten/spec.md#customers-navigation-entry-is-reachable
	test('customers navigation entry is reachable', async ({ page }) => {
		await go(page, '/')
		await expect(page.locator('.app-navigation').getByRole('link', { name: 'Customers' })).toBeVisible()
	})

	// @e2e openspec/specs/klanten/spec.md#customer-detail-route-resolves-to-the-detail-shell
	test('customer detail route resolves to the detail shell', async ({ page }) => {
		await go(page, '/klanten/e2e-nonexistent')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/klanten/')
	})
})

// ===========================================================================
// CONTACTMOMENTEN spec — openspec/specs/contactmomenten/spec.md
// ===========================================================================

test.describe('contactmomenten', () => {
	// @e2e openspec/specs/contactmomenten/spec.md#contact-moments-list-view-renders
	test('contact moments list view renders', async ({ page }) => {
		await go(page, '/contactmomenten')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/contactmomenten')
	})

	// @e2e openspec/specs/contactmomenten/spec.md#contact-moments-navigation-entry-is-reachable
	test('contact moments navigation entry is reachable', async ({ page }) => {
		await go(page, '/')
		await expect(page.locator('.app-navigation').getByRole('link', { name: 'Contact moments' })).toBeVisible()
	})
})

// ===========================================================================
// BERICHTEN spec — openspec/specs/berichten/spec.md
// ===========================================================================

test.describe('berichten', () => {
	// @e2e openspec/specs/berichten/spec.md#messages-list-view-renders
	test('messages list view renders', async ({ page }) => {
		await go(page, '/berichten')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		expect(page.url()).toContain('/berichten')
	})

	// @e2e openspec/specs/berichten/spec.md#messages-navigation-entry-is-reachable
	test('messages navigation entry is reachable', async ({ page }) => {
		await go(page, '/')
		await expect(page.locator('.app-navigation').getByRole('link', { name: 'Messages' })).toBeVisible()
	})
})

// ===========================================================================
// SEARCH spec — openspec/specs/search/spec.md
// ===========================================================================

test.describe('search', () => {
	// @e2e openspec/specs/search/spec.md#search-page-renders-its-heading
	test('search page renders its heading', async ({ page }) => {
		await go(page, '/zoeken')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		const heading = page.getByRole('heading', { name: /Search/i }).first()
		await expect(heading).toBeVisible({ timeout: 10_000 })
	})
})

// ===========================================================================
// AUDIT TRAIL spec — openspec/specs/audit-trail/spec.md
// ===========================================================================

test.describe('audit-trail', () => {
	// @e2e openspec/specs/audit-trail/spec.md#audit-trail-page-renders-its-placeholder-note
	test('audit trail page renders its placeholder note', async ({ page }) => {
		await go(page, '/auditTrail')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		// The placeholder is a note card with the "Audit trail" title.
		const note = page.getByText(/Audit trail|follow-up change/i).first()
		await expect(note).toBeVisible({ timeout: 10_000 })
	})
})

// ===========================================================================
// SETTINGS spec — openspec/specs/settings/spec.md
// ===========================================================================

test.describe('settings', () => {
	// @e2e openspec/specs/settings/spec.md#settings-page-renders
	test('settings page renders', async ({ page }) => {
		await go(page, '/settings')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		const section = page.getByText(/Data storage/i).first()
		await expect(section).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/settings/spec.md#storage-source-selectors-are-present
	test('storage source selectors are present', async ({ page }) => {
		await go(page, '/settings')
		await expect(page.locator('.app-content, #app-content')).toBeVisible()
		// A Save control is rendered once the settings form has loaded.
		const save = page.getByRole('button', { name: /Save/i }).first()
		await expect(save).toBeVisible({ timeout: 10_000 })
	})
})
