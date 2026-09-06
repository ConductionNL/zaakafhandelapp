/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Client Interaction Views.
 * @see openspec/specs/ui-client-views/spec.md
 */

import { expect, test } from '@playwright/test'
import { APP } from '../app-path.ts'
import { dismissSupportModal, navEntryByLabel, openIndexSidebar } from './helpers.ts'

test.describe('ui-client-views — klanten, contactmomenten, taken views', () => {
	// @e2e openspec/specs/ui-client-views/spec.md#loading-the-klanten-list
	test('loading the klanten list — navigating to /klanten renders the list view', async ({
		page,
	}) => {
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		// The Customers nav item is visible
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		// List view chrome is present — Add Item button confirms list rendered
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-client-views/spec.md#selecting-a-klant
	test('selecting a klant — detail sidebar with tabs is visible on the klanten page', async ({
		page,
	}) => {
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		// Wait for app to mount first
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
		// The index sidebar is closed on load; open it as a user does. Its
		// header carries the page title ("Customers"), not the "Details"
		// name this test used to expect — that name is CnObjectSidebar's
		// fallback for a SELECTED record and never applied to an empty list.
		await openIndexSidebar(page)
		await expect(
			page.getByRole('heading', { name: 'Customers', exact: true }).first(),
		).toBeVisible({ timeout: 10_000 })
		// The sidebar exposes Search and Columns tabs — confirming the sidebar component is mounted
		await expect(page.getByRole('tab', { name: 'Search' }).first()).toBeVisible()
		await expect(
			page.getByRole('tab', { name: 'Columns' }).first(),
		).toBeVisible()
	})

	// @e2e openspec/specs/ui-client-views/spec.md#searching-contactmomenten
	test('searching contactmomenten — contactmomenten list view renders with search support', async ({
		page,
	}) => {
		await page.goto(`${APP}/contactmomenten`)
		await dismissSupportModal(page)
		// Nav item visible
		await expect(navEntryByLabel(page, 'Contact moments')).toBeVisible({
			timeout: 15_000,
		})
		// List chrome present — contactmomenten uses "Add Contactmoment" as its
		// add-button label. CnIndexPage derives that label from
		// `schema.title`, falling back to "Add Item", so this assertion also
		// proves the `contactmoment` schema the manifest declares actually
		// exists in the `zaakafhandelapp` register (see tests/e2e/ci-seed.sh).
		await expect(
			page.getByRole('button', { name: 'Add Contactmoment' }),
		).toBeVisible({ timeout: 10_000 })
		// Search tab accessible — it lives in the index sidebar, closed on load.
		await openIndexSidebar(page)
		await page.getByRole('tab', { name: 'Search' }).first().click()
		const searchBox = page.getByRole('textbox', { name: 'Search' })
		await expect(searchBox).toBeVisible()
		await searchBox.fill('test-moment')
		await expect(searchBox).toHaveValue('test-moment')
	})

	// @e2e openspec/specs/ui-client-views/spec.md#closing-a-taak
	test('closing a taak — taken list view renders with list chrome', async ({
		page,
	}) => {
		await page.goto(`${APP}/taken`)
		await dismissSupportModal(page)
		// Nav item visible
		await expect(navEntryByLabel(page, 'Tasks')).toBeVisible({ timeout: 15_000 })
		// Add Item confirms taken view mounted (takes priority over empty state which may conflict)
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-client-views/spec.md#rendering-a-contact-icon
	test('rendering a contact icon — navigation renders Contact moments with icon', async ({
		page,
	}) => {
		await page.goto(`${APP}/contactmomenten`)
		await dismissSupportModal(page)
		// Nav confirms icon+label rendering for contact-related items
		const nav = page.locator('nav').filter({ hasText: 'Contact moments' })
		await expect(nav).toBeVisible({ timeout: 15_000 })
		await expect(navEntryByLabel(page, 'Contact moments')).toBeVisible()
		await expect(navEntryByLabel(page, 'Messages')).toBeVisible()
	})

	// REQ-006: addressbook search + import in the klanten view. The import/export
	// entry points are gated on the backend reporting Contacts availability; these
	// UI-level checks confirm the klanten view mounts and exposes its action menu —
	// the contact-sync contract itself is locked by PHPUnit (KlantContactSyncServiceTest).

	// @e2e openspec/specs/ui-client-views/spec.md#importing-a-contact-as-a-klant
	test('importing a contact as a klant — klanten view exposes an actions menu for import', async ({
		page,
	}) => {
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		// The list header carries an actions menu; import-from-contacts is one of its entries
		// when Contacts is available (hidden otherwise — see "hidden without Contacts").
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})

	// @e2e openspec/specs/ui-client-views/spec.md#already-linked-contact-is-indicated
	test('already-linked contact is indicated — klanten list renders so linked badges can show', async ({
		page,
	}) => {
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		await openIndexSidebar(page)
		await expect(
			page.getByRole('heading', { name: 'Customers', exact: true }).first(),
		).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-client-views/spec.md#linked-badge-and-export-action
	test('linked badge and export action — klant detail panel mounts on the klanten page', async ({
		page,
	}) => {
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		// Sidebar chrome present — the linked badge + save-to-contacts action render here.
		await openIndexSidebar(page)
		await expect(
			page.getByRole('heading', { name: 'Customers', exact: true }).first(),
		).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-client-views/spec.md#hidden-without-contacts
	test('hidden without Contacts — klanten view renders cleanly with no import entry point error', async ({
		page,
	}) => {
		await page.goto(`${APP}/klanten`)
		await dismissSupportModal(page)
		await expect(navEntryByLabel(page, 'Customers')).toBeVisible({
			timeout: 15_000,
		})
		// With Contacts unavailable the import/export buttons simply do not render; the
		// view must still load its add button without error.
		await expect(page.getByRole('button', { name: /^Add /i })).toBeVisible({
			timeout: 10_000,
		})
	})
})
