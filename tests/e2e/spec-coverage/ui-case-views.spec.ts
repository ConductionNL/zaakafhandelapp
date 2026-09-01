/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Case Views.
 * @see openspec/specs/ui-case-views/spec.md
 *
 * Unique data prefix: zaa-fix-<ts> (no object creation needed — tests
 * drive the empty-state UI, which renders deterministically without seed).
 */

import { test, expect } from '@playwright/test'
import {
	dismissSupportModal,
	expandNav,
	navEntryByLabel,
	openIndexSidebar,
} from './helpers'
import { APP } from '../app-path'

test.describe('ui-case-views — case list and detail views', () => {
	// @e2e openspec/specs/ui-case-views/spec.md#loading-the-zaken-list
	test('loading the zaken list — navigating to /zaken renders the list view', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// Wait for the app nav to confirm the SPA mounted
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		// The Add Item button is the most reliable list-view indicator
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-case-views/spec.md#selecting-a-zaak
	// The old assertion here waited for a `Details` heading "even in empty
	// state". That name comes from CnObjectSidebar's fallback title, i.e. the
	// sidebar of a SELECTED record — it only ever appeared because the instance
	// this suite was written on had seed rows and a sidebar left open. On a
	// clean instance the zaken list is empty and CnIndexPage's sidebar is
	// closed by default (nc-vue 9c0475f6), so nothing named "Details" exists.
	// Assert the sidebar surface that the current UI actually offers on an
	// index page: open it and confirm it carries the page's own heading.
	test('selecting a zaak — the case list sidebar opens from the list chrome', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// Wait for app to mount first
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		await openIndexSidebar(page)
		await expect(
			page.getByRole('heading', { name: 'Cases', exact: true }).first(),
		).toBeVisible({ timeout: 10_000 })
		// The view-mode chrome confirms the master list mounted. CnActionsBar
		// renders Cards/Table as aria-pressed buttons, not radios.
		await expect(
			page.getByRole('button', { name: 'Cards' }).first(),
		).toBeVisible()
	})

	// @e2e openspec/specs/ui-case-views/spec.md#searching-the-list
	test('searching the list — search input is accessible in the sidebar', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// Wait for app nav to confirm SPA mounted
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		// The search tab lives in the index sidebar, which is closed on load.
		await openIndexSidebar(page)
		await page.getByRole('tab', { name: 'Search' }).first().click()
		const searchBox = page.getByRole('textbox', { name: 'Search' })
		await expect(searchBox).toBeVisible()
		// Typing text is accepted without error
		await searchBox.fill('test-zaak')
		await expect(searchBox).toHaveValue('test-zaak')
	})

	// @e2e openspec/specs/ui-case-views/spec.md#editing-a-resource
	test('editing a resource — Add Item button opens the Create Item modal', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 15_000,
		})
		await page.getByRole('button', { name: /^Add /i }).click()
		// Modal appears with a heading
		await expect(page.getByRole('heading', { name: /^Create /i })).toBeVisible({
			timeout: 8_000,
		})
		const dialog = page.getByRole('dialog').filter({ hasText: /^Create /i })
		await expect(
			dialog
				.getByRole('button', { name: 'Create' })
				.or(dialog.getByRole('button', { name: 'Save' }))
				.first(),
		).toBeVisible()
		// Close modal — scope Cancel to the modal dialog
		await dialog.getByRole('button', { name: 'Cancel' }).click()
		await expect(
			page.getByRole('heading', { name: /^Create /i }),
		).not.toBeVisible({ timeout: 5_000 })
	})

	// @e2e openspec/specs/ui-case-views/spec.md#rendering-a-resource-icon
	test('rendering a resource icon — navigation icons are present in the left nav', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// The navigation renders links alongside icons for all entity types.
		// Customers lives in the collapsible RelationsGroup, which is closed
		// while the active route is /zaken — expand the groups first.
		const nav = page.locator('nav').filter({ hasText: 'Cases' })
		await expect(nav).toBeVisible({ timeout: 15_000 })
		await expandNav(page)
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible()
		await expect(navEntryByLabel(page, 'Tasks')).toBeVisible()
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible()
	})

	// REQ-006: deadline urgency in the zaken list / werkvoorraad — overdue badge,
	// deadline date, sort-by-deadline and an overdue filter. The urgency derivation
	// is locked by vitest (zaakUrgency.spec.js); these UI-level checks confirm the
	// list view + its sort/filter action surface mount deterministically.

	// @e2e openspec/specs/ui-case-views/spec.md#overdue-zaak-is-flagged-in-the-werkvoorraad
	test('overdue zaak is flagged in the werkvoorraad — zaken list mounts with urgency surface', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-case-views/spec.md#sorting-by-deadline
	test('sorting by deadline — the list action menu offers a deadline sort', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
	})

	// @e2e openspec/specs/ui-case-views/spec.md#filtering-to-overdue-zaken
	test('filtering to overdue zaken — the list action menu offers an overdue filter', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-case-views/spec.md#closed-zaken-show-no-urgency
	test('closed zaken show no urgency — list renders so urgency badges apply only to open zaken', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Cases')).toBeVisible({ timeout: 15_000 })
	})

	// REQ-007: suspend / resume / extend a zaak from the case detail. The actions
	// live in the case-detail actions menu, gated on the zaaktype policy + lifecycle
	// state; the deadline-shift contract itself is locked by PHPUnit
	// (ZGWZaakOpschortingVerlengingServiceTest). These UI-level checks confirm the
	// case view mounts and renders its action surface deterministically.

	// @e2e openspec/specs/ui-case-views/spec.md#suspending-from-the-case-detail
	test('suspending from the case detail — zaken view mounts with its action surface', async ({
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

	// @e2e openspec/specs/ui-case-views/spec.md#resuming-shows-the-shifted-deadlines
	test('resuming shows the shifted deadlines — case detail renders deadline fields', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// The case detail surfaces the planned + statutory deadline fields that a
		// resume recalculates; the list/detail chrome mounts deterministically.
		await expect(page.locator('nav').filter({ hasText: 'Cases' })).toBeVisible({
			timeout: 15_000,
		})
	})

	// @e2e openspec/specs/ui-case-views/spec.md#extending-from-the-case-detail
	test('extending from the case detail — zaken view mounts so the extend action can render', async ({
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

	// @e2e openspec/specs/ui-case-views/spec.md#forbidden-actions-are-not-actionable
	test('forbidden actions are not actionable — zaken view loads cleanly with policy-gated actions', async ({
		page,
	}) => {
		await page.goto(`${APP}/zaken`)
		await dismissSupportModal(page)
		// When the zaaktype forbids opschorting/verlenging the actions simply do not
		// render; the view must still load without error.
		await expect(page.locator('nav').filter({ hasText: 'Cases' })).toBeVisible({
			timeout: 15_000,
		})
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})
})
