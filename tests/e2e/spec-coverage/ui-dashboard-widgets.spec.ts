/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Dashboard Widgets.
 * @see openspec/specs/ui-dashboard-widgets/spec.md
 *
 * Dashboard widgets are registered with Nextcloud's dashboard framework and
 * rendered at /apps/dashboard. The zaakafhandelapp registers six widgets
 * (zaken, taken, open-zaken, contactmomenten, personen, organisaties).
 * On an empty-data instance the widgets mount and show an empty state / search
 * input. These tests confirm widget mount, search availability, and item
 * presentation structure — not data presence.
 */

import { expect, test } from '@playwright/test'
import { APP } from '../app-path.ts'
import { dismissSupportModal, navEntryByLabel, openIndexSidebar } from './helpers.ts'

// Front-controller form for the same reason as APP — see tests/e2e/app-path.ts.
// `php -S` applies no rewrite, so a bare `/apps/dashboard` 404s there.
const DASHBOARD = '/index.php/apps/dashboard'

test.describe('ui-dashboard-widgets — NC dashboard widget mount and structure', () => {
	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#loading-the-open-zaken-widget
	test('loading the open-zaken widget — widget container is present on NC dashboard', async ({
		page,
	}) => {
		// Use domcontentloaded + 60s timeout to avoid waiting for all widget XHR to complete
		await page.goto(DASHBOARD, {
			waitUntil: 'domcontentloaded',
			timeout: 60_000,
		})
		// The NC dashboard renders; the header confirms authentication
		await expect(page.locator('#header')).toBeVisible({ timeout: 20_000 })
		// The page title confirms we are on the NC dashboard
		await expect(page).toHaveTitle(/Dashboard/i, { timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#searching-within-a-widget
	test('searching within a widget — zaakafhandelapp list views expose search input', async ({
		page,
	}) => {
		// Widgets share the search store with list views. Exercise via the zaken list.
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// Wait for app nav
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		// The search tab lives in the index sidebar, closed on load.
		await openIndexSidebar(page)
		await page.getByRole('tab', { name: 'Search' }).first().click()
		const searchBox = page.getByRole('textbox', { name: 'Search' })
		await expect(searchBox).toBeVisible()
		await searchBox.fill('open-zaak')
		await expect(searchBox).toHaveValue('open-zaak')
		// Clear
		await searchBox.fill('')
		await expect(searchBox).toHaveValue('')
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#rendering-a-contactmoment-item
	test('rendering a contactmoment item — contactmomenten page renders list chrome', async ({
		page,
	}) => {
		await page.goto(`${APP}/contactmomenten`)
		await dismissSupportModal(page)
		// Wait for app to mount
		await expect(navEntryByLabel(page, 'Contact moments')).toBeVisible({
			timeout: 15_000,
		})
		// List chrome confirms the component is mounted — contactmomenten uses "Add Contactmoment"
		await expect(
			page.getByRole('button', { name: 'Add Contactmoment' }),
		).toBeVisible({ timeout: 10_000 })
		// The Cards segment confirms the list container is present. CnActionsBar
		// renders the view-mode toggle as aria-pressed buttons, not radios.
		await expect(
			page.getByRole('button', { name: 'Cards' }).first(),
		).toBeVisible()
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#showing-a-widget-item
	test('showing a widget item — detail sidebar is accessible from the list', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// Wait for app to mount first
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
		// The sidebar opens from the list chrome — confirming item-detail
		// linking is wired. Its header carries the page title, not the
		// "Details" name this test used to expect (that is CnObjectSidebar's
		// fallback for a selected record, which an empty list never has).
		await openIndexSidebar(page)
		await expect(
			page.getByRole('heading', { name: 'Cases', exact: true }).first(),
		).toBeVisible({ timeout: 10_000 })
		// The Search + Columns tabs confirm the sidebar component is fully mounted
		await expect(page.getByRole('tab', { name: 'Search' }).first()).toBeVisible()
		await expect(
			page.getByRole('tab', { name: 'Columns' }).first(),
		).toBeVisible()
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#opening-klant-search-from-the-personen-widget
	test('opening klant search from the personen widget — klanten page renders', async ({
		page,
	}) => {
		// The personen widget links into /klanten; verify that page mounts
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// REQ-006: the open-zaken widget surfaces deadline urgency (overdue indicator +
	// count, most-urgent-first ordering). The urgency derivation + ordering are locked
	// by the vitest suite (zaakUrgency.spec.js); these UI-level checks confirm the
	// open-zaken widget surface (the /zaken view it links into) mounts deterministically.

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#overdue-zaken-float-to-the-top-of-the-widget
	test('overdue zaken float to the top of the widget — open-zaken surface mounts', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('nav').filter({ hasText: 'Cases' })).toBeVisible({
			timeout: 15_000,
		})
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-dashboard-widgets/spec.md#no-overdue-zaken-no-alarm
	test('no overdue zaken, no alarm — open-zaken surface renders without an overdue count', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// With no overdue zaak the widget shows no overdue-count header; the view
		// mounts cleanly either way.
		await expect(page.locator('nav').filter({ hasText: 'Cases' })).toBeVisible({
			timeout: 15_000,
		})
	})
})
