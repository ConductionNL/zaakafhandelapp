/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * UI helpers for the manifest-v2 (CnAppRoot) index / create-modal / row-actions
 * surfaces that the deep workflow specs drive.
 *
 * The manifest renderer generates the index list, the create/edit modal and the
 * per-row actions menu generically from each page's OpenRegister schema, so
 * these helpers target the shared, stable chrome discovered by live inspection:
 *
 *   [data-testid="cn-index-page"]      the index page host
 *   [data-testid="cn-object-list-table"] the Table-mode list
 *   [data-testid="cn-object-row"]      one record row (renders its column values)
 *   [data-testid="cn-row-actions"]     the per-row ⋯ menu trigger (View/Edit/Copy/Delete)
 *   "Add <schema>" button              opens the "Create <schema>" modal
 *   "Create <schema>" / "Edit <schema>" dialog  the generated form (labels = schema property titles)
 *
 * A record's field values render directly in its Table row, which is the
 * data-dependent surface the CRUD-persistence specs assert against.
 */

import { type Page, type Locator, expect } from '@playwright/test'
import { dismissSupportModal } from '../spec-coverage/helpers'
import { APP } from '../app-path'

export { APP }

/** A roomy viewport so the master list isn't squeezed by the detail sidebars. */
export async function widen(page: Page): Promise<void> {
	await page.setViewportSize({ width: 1600, height: 1000 })
}

/** Open an index page and wait for the manifest index host to mount.
 *
 * The Vue router runs in hash mode (src/router/index.js → mode: 'hash'), so
 * the in-app route MUST be carried in the URL fragment. A path-form goto
 * (`/apps/zaakafhandelapp/zaken`) boots the SPA with an empty hash and the
 * router falls back to the Dashboard, so the target index never mounts.
 * Deep-link via the hash instead.
 *
 * A hash-only `page.goto` is a same-document navigation: it swaps the route
 * (and therefore the index columns) but the list store does NOT always re-fetch
 * the new schema's objects when navigating index→index — it can keep rendering
 * the previous page's rows under the new columns (e.g. zaken rows under taak
 * columns, so the `title` column shows "—" and the count is the wrong schema's).
 * Force a document reload so the target index loads its own data cleanly.
 */
export async function openIndex(page: Page, plural: string): Promise<Locator> {
	await widen(page)
	await page.goto(`${APP}/#/${plural}`)
	await page.reload()
	await dismissSupportModal(page)
	const index = page.locator('[data-testid="cn-index-page"]')
	await expect(index).toBeVisible({ timeout: 15_000 })
	return index
}

/** Switch the index master list to Table mode so rows render as cn-object-row. */
export async function useTableView(page: Page): Promise<void> {
	const tableTab = page
		.getByRole('button', { name: 'Table', exact: true })
		.or(page.getByText('Table', { exact: true }))
	if (
		await tableTab
			.first()
			.isVisible({ timeout: 3_000 })
			.catch(() => false)
	) {
		await tableTab.first().click()
		await page.waitForTimeout(500)
	}
}

/** Open the "Add <schema>" create modal and return the dialog locator. */
export async function openCreateModal(page: Page): Promise<Locator> {
	await page.getByRole('button', { name: /^Add /i }).first().click()
	const dialog = page.getByRole('dialog').first()
	await expect(dialog.getByRole('heading', { name: /^Create /i })).toBeVisible({
		timeout: 8_000,
	})
	return dialog
}

/**
 * Fill a generated NcTextField inside a modal by its label text. The manifest
 * form renders each schema property as a field whose wrapper contains the
 * property title (e.g. "naam") and a single text input.
 */
export async function fillField(
	scope: Locator,
	label: string,
	value: string,
): Promise<void> {
	const byRole = scope
		.getByRole('textbox', { name: new RegExp(`^${label}$`, 'i') })
		.first()
	if (
		(await byRole.count()) > 0
		&& (await byRole.isVisible().catch(() => false))
	) {
		await byRole.fill(value)
		return
	}
	const field = scope
		.locator('.input-field, [class*="input-field"], [class*="field"]')
		.filter({ hasText: new RegExp(`^\\s*${label}\\s*$`, 'i') })
		.locator('input, textarea')
		.first()
	await field.fill(value)
}

/** Click the modal's Create/Save submit button. */
export async function submitModal(dialog: Locator): Promise<void> {
	const btn = dialog
		.getByRole('button', { name: 'Create' })
		.or(dialog.getByRole('button', { name: 'Save' }))
		.or(dialog.getByRole('button', { name: 'Update' }))
		.first()
	await btn.click()
}

/** Click the modal's Cancel button. */
export async function cancelModal(dialog: Locator): Promise<void> {
	await dialog.getByRole('button', { name: 'Cancel' }).first().click()
}

/** Locate the first Table row whose text contains `term` (Table mode forced). */
export async function rowFor(
	page: Page,
	index: Locator,
	term: string,
): Promise<Locator> {
	await useTableView(page)
	return index.locator('[data-testid="cn-object-row"]', { hasText: term }).first()
}

/** Whether any Table row contains `term`. */
export async function indexContains(
	page: Page,
	index: Locator,
	term: string,
): Promise<boolean> {
	await useTableView(page)
	const text = await index
		.locator('[data-testid="cn-object-list-table"]')
		.textContent()
		.catch(() => '')
	return (text || '').includes(term)
}

/** Open a row's actions menu and click one item (View/Edit/Copy/Delete). */
export async function rowAction(
	page: Page,
	row: Locator,
	action: 'View' | 'Edit' | 'Copy' | 'Delete',
): Promise<void> {
	await row
		.locator(
			'[data-testid="cn-row-actions"] button, [data-testid="cn-row-actions"]',
		)
		.first()
		.click()
	await page.waitForTimeout(400)
	await page
		.getByRole('menuitem', { name: action, exact: true })
		.or(page.getByRole('button', { name: action, exact: true }))
		.first()
		.click()
}
