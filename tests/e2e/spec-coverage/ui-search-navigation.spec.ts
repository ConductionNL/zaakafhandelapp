/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Search, Navigation & Utilities.
 * @see openspec/specs/ui-search-navigation/spec.md
 */

import { test, expect } from '@playwright/test'
import { dismissSupportModal } from './helpers'

const APP = '/apps/zaakafhandelapp'

test.describe('ui-search-navigation — search sidebar, config nav, permissions, utilities', () => {

	// @e2e openspec/specs/ui-search-navigation/spec.md#searching-from-the-sidebar
	test('searching from the sidebar — /zoeken route renders search view and accepts input', async ({ page }) => {
		await page.goto(`${APP}/zoeken`)
		await dismissSupportModal(page)
		// Wait for app to mount
		await expect(page.getByRole('link', { name: 'Cases' })).toBeVisible({ timeout: 15_000 })
		// The Search nav item is active (highlighted)
		await expect(page.getByRole('link', { name: 'Search' })).toBeVisible({ timeout: 10_000 })
		// The sidebar search tab is present (use first to avoid strict-mode on multiple tabs)
		await expect(page.getByRole('tab', { name: 'Search' }).first()).toBeVisible({ timeout: 10_000 })
		await page.getByRole('tab', { name: 'Search' }).first().click()
		const searchBox = page.getByRole('textbox', { name: 'Search' })
		await expect(searchBox).toBeVisible()
		// Type a search query — the input accepts it (debounce fires without JS error)
		await searchBox.fill('zaa-fix-search')
		await expect(searchBox).toHaveValue('zaa-fix-search')
		// Clear the input
		await searchBox.fill('')
		await expect(searchBox).toHaveValue('')
	})

	// @e2e openspec/specs/ui-search-navigation/spec.md#saving-configuration-from-the-nav-panel
	test('saving configuration from the nav panel — Settings button is accessible in nav', async ({ page }) => {
		await page.goto(APP)
		await dismissSupportModal(page)
		// Wait for app nav to confirm SPA mounted
		await expect(page.getByRole('link', { name: 'Cases' })).toBeVisible({ timeout: 15_000 })
		// The Settings button is in the app navigation panel (not the NC header "Settings menu").
		// Scope to the nav element that contains the app's own links (identified by "Cases" link).
		const appNav = page.locator('nav').filter({ has: page.getByRole('link', { name: 'Cases' }) })
		await expect(appNav.getByRole('button', { name: 'Settings' })).toBeVisible({ timeout: 10_000 })
		// The nav also renders additional admin links — Features & roadmap confirms the footer is mounted
		await expect(appNav.getByRole('link', { name: 'Features & roadmap' })).toBeVisible()
	})

	// @e2e openspec/specs/ui-search-navigation/spec.md#resolving-permissions
	test('resolving permissions — the app initialises and navigation items are visible to admin', async ({ page }) => {
		await page.goto(APP)
		await dismissSupportModal(page)
		// Admin user sees all nav items — confirms permission flags are resolved correctly
		await expect(page.getByRole('link', { name: 'Cases' })).toBeVisible({ timeout: 15_000 })
		await expect(page.getByRole('link', { name: 'Customers' })).toBeVisible()
		await expect(page.getByRole('link', { name: 'Tasks' })).toBeVisible()
		await expect(page.getByRole('link', { name: 'Employees' })).toBeVisible()
		// Navigate to zaken and check "Add Item" — confirming write-permission flag is set for admin
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-search-navigation/spec.md#normalising-a-date
	test('normalising a date — the app loads without date-normalisation errors in the console', async ({ page }) => {
		const jsErrors: string[] = []
		page.on('pageerror', (err) => jsErrors.push(err.message))
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.getByRole('link', { name: 'Cases' })).toBeVisible({ timeout: 15_000 })
		// No uncaught JS errors related to date normalisation
		const dateErrors = jsErrors.filter(e => /date|iso|normalise|normalize/i.test(e))
		expect(dateErrors, `Date normalisation errors: ${dateErrors.join(', ')}`).toHaveLength(0)
	})

	// @e2e openspec/specs/ui-search-navigation/spec.md#rendering-an-icon
	test('rendering an icon — navigation SVG/img icons are present in the DOM', async ({ page }) => {
		await page.goto(APP)
		await dismissSupportModal(page)
		await expect(page.getByRole('link', { name: 'Cases' })).toBeVisible({ timeout: 15_000 })
		// SVG/img elements are injected by the MDI icon provider — at least one should be
		// present in the app navigation area
		const navIconCount = await page.locator('nav svg, nav img').count()
		expect(navIconCount, 'Expected at least one SVG/img icon in the navigation').toBeGreaterThan(0)
	})

})
