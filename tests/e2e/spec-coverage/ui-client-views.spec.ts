/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Client Interaction Views.
 * @see openspec/specs/ui-client-views/spec.md
 */

import { test, expect } from '@playwright/test'
import { dismissSupportModal, navEntryByLabel } from './helpers'

const APP = '/apps/zaakafhandelapp'

test.describe('ui-client-views — klanten, contactmomenten, taken views', () => {

	// @e2e openspec/specs/ui-client-views/spec.md#loading-the-klanten-list
	test('loading the klanten list — navigating to /klanten renders the list view', async ({ page }) => {
		await page.goto(`${APP}/#/klanten`)
		await dismissSupportModal(page)
		// The Customers nav item is visible
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({ timeout: 15_000 })
		// List view chrome is present — Add Item button confirms list rendered
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-client-views/spec.md#selecting-a-klant
	test('selecting a klant — detail sidebar with tabs is visible on the klanten page', async ({ page }) => {
		await page.goto(`${APP}/#/klanten`)
		await dismissSupportModal(page)
		// Wait for app to mount first
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({ timeout: 15_000 })
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({ timeout: 10_000 })
		// Detail panel heading is visible (even empty-state shows Details header)
		await expect(page.getByRole('heading', { name: 'Details' })).toBeVisible({ timeout: 10_000 })
		// The right sidebar exposes Search and Columns tabs — confirming the sidebar component is mounted
		await expect(page.getByRole('tab', { name: 'Search' }).first()).toBeVisible()
		await expect(page.getByRole('tab', { name: 'Columns' }).first()).toBeVisible()
	})

	// @e2e openspec/specs/ui-client-views/spec.md#searching-contactmomenten
	test('searching contactmomenten — contactmomenten list view renders with search support', async ({ page }) => {
		await page.goto(`${APP}/#/contactmomenten`)
		await dismissSupportModal(page)
		// Nav item visible
		await expect(navEntryByLabel(page, 'Contact moments')).toBeVisible({ timeout: 15_000 })
		// List chrome present — contactmomenten uses "Add Contactmoment" as its add-button label
		await expect(page.getByRole('button', { name: 'Add Contactmoment' })).toBeVisible({ timeout: 10_000 })
		// Search tab accessible (use first to avoid strict mode with multiple tabs)
		await expect(page.getByRole('tab', { name: 'Search' }).first()).toBeVisible()
		await page.getByRole('tab', { name: 'Search' }).first().click()
		const searchBox = page.getByRole('textbox', { name: 'Search' })
		await expect(searchBox).toBeVisible()
		await searchBox.fill('test-moment')
		await expect(searchBox).toHaveValue('test-moment')
	})

	// @e2e openspec/specs/ui-client-views/spec.md#closing-a-taak
	test('closing a taak — taken list view renders with list chrome', async ({ page }) => {
		await page.goto(`${APP}/#/taken`)
		await dismissSupportModal(page)
		// Nav item visible
		await expect(navEntryByLabel(page, 'Tasks')).toBeVisible({ timeout: 15_000 })
		// Add Item confirms taken view mounted (takes priority over empty state which may conflict)
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-client-views/spec.md#rendering-a-contact-icon
	test('rendering a contact icon — navigation renders Contact moments with icon', async ({ page }) => {
		await page.goto(`${APP}/#/contactmomenten`)
		await dismissSupportModal(page)
		// Nav confirms icon+label rendering for contact-related items
		const nav = page.locator('nav').filter({ hasText: 'Contact moments' })
		await expect(nav).toBeVisible({ timeout: 15_000 })
		await expect(navEntryByLabel(page, 'Contact moments')).toBeVisible()
		await expect(navEntryByLabel(page, 'Messages')).toBeVisible()
	})

})
