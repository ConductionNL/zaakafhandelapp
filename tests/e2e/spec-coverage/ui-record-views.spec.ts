/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Record (index) Views.
 *
 * Covers the manifest index pages that were not exercised by the
 * existing spec-coverage suite (which covered zaken/klanten/taken/
 * contactmomenten/search): medewerkers, berichten, rollen, zaaktypen,
 * besluiten, documenten, resultaten, statussen.
 *
 * These pages are rendered generically by the manifest-v2 CnAppRoot
 * shell from the `index`-type page config in src/manifest.json. The list
 * chrome (Add button, Cards/Table view-mode radios, right-hand Details
 * sidebar with Search + Columns tabs) renders deterministically without
 * any seed data, so all assertions are data-independent.
 *
 * Routing note: `appinfo/routes.php` only registers server-side page
 * routes for a subset of manifest pages. medewerkers, statussen and
 * zaaktypen have server routes and are reachable via a hard `page.goto`.
 * besluiten/documenten/resultaten have NO server route (a hard goto 404s
 * — see BUG-1 in the audit report), so they are reached the way a real
 * user reaches them: through the in-app Vue router via `spaNavigate`.
 *
 * @see openspec/specs/ui-case-views/spec.md (shared index-view contract)
 * @see openspec/specs/domain-entities/spec.md
 */

import { test, expect, type Page } from '@playwright/test'
import { dismissSupportModal, spaNavigate } from './helpers'

const APP = '/apps/zaakafhandelapp'

/**
 * Assert the shared index-view chrome on the page currently loaded.
 * `title` is the page heading rendered by the index page (e.g. "Employees").
 */
async function assertIndexChrome(page: Page, title: string): Promise<void> {
	await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
	// The index page host confirms the manifest index page mounted.
	await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({ timeout: 10_000 })
	// The page heading is the most direct proof the right page rendered.
	await expect(page.getByRole('heading', { name: title, exact: true }).first()).toBeVisible({ timeout: 10_000 })
	// The primary create button is the canonical list-view action.
	await expect(page.getByRole('button', { name: 'Add Item' }).first()).toBeVisible({ timeout: 10_000 })
	// The view-mode chrome (Cards radio) confirms the master list mounted.
	await expect(page.getByRole('radio', { name: 'Cards' }).first()).toBeVisible({ timeout: 10_000 })
	// The detail sidebar renders even in empty state.
	await expect(page.getByRole('heading', { name: 'Details' })).toBeVisible({ timeout: 10_000 })
}

/** Server-routed index page: reachable via a hard goto. */
async function gotoIndex(page: Page, route: string, title: string): Promise<void> {
	await page.goto(`${APP}${route}`)
	await dismissSupportModal(page)
	await assertIndexChrome(page, title)
}

test.describe('ui-record-views — generic index pages render shared list chrome', () => {

	// @e2e openspec/specs/domain-entities/spec.md#medewerker
	test('medewerkers — Employees index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/medewerkers', 'Employees')
	})

	// @e2e openspec/specs/domain-entities/spec.md#bericht
	test('berichten — Messages index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/berichten', 'Messages')
	})

	// @e2e openspec/specs/domain-entities/spec.md#rol
	test('rollen — Roles index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/rollen', 'Roles')
	})

	// @e2e openspec/specs/domain-entities/spec.md#zaaktype
	test('zaaktypen — Case types index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/zaaktypen', 'Case types')
	})

	// @e2e openspec/specs/domain-entities/spec.md#status
	test('statussen — Statuses index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/statussen', 'Statuses')
	})

	// @e2e openspec/specs/domain-entities/spec.md#besluit
	test('besluiten — Decisions index renders list chrome (via in-app nav)', async ({ page }) => {
		// No server-side page route (BUG-1): reach it through the Vue router.
		await spaNavigate(page, '/besluiten')
		await assertIndexChrome(page, 'Decisions')
	})

	// @e2e openspec/specs/domain-entities/spec.md#document
	test('documenten — Documents index renders list chrome (via in-app nav)', async ({ page }) => {
		await spaNavigate(page, '/documenten')
		await assertIndexChrome(page, 'Documents')
	})

	// @e2e openspec/specs/domain-entities/spec.md#resultaat
	test('resultaten — Results index renders list chrome (via in-app nav)', async ({ page }) => {
		await spaNavigate(page, '/resultaten')
		await assertIndexChrome(page, 'Results')
	})

	// @e2e openspec/specs/ui-case-views/spec.md#table-view-mode
	test('view-mode toggle — Table radio switches the medewerkers list to table mode', async ({ page }) => {
		await page.goto(`${APP}/medewerkers`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const tableRadio = page.getByRole('radio', { name: 'Table' }).first()
		await expect(tableRadio).toBeVisible({ timeout: 10_000 })
		// The radio input itself is visually hidden behind its label in the
		// NcCheckboxRadioSwitch; click the associated label to toggle it.
		await tableRadio.click({ force: true })
		await expect(tableRadio).toBeChecked()
		// The list chrome survives the mode switch.
		await expect(page.getByRole('radio', { name: 'Cards' }).first()).toBeVisible()
	})

	// @e2e openspec/specs/ui-modals/spec.md#opening-a-create-modal
	test('rollen create modal — Add button opens the create dialog and cancels cleanly', async ({ page }) => {
		await page.goto(`${APP}/rollen`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
		const addBtn = page.getByRole('button', { name: 'Add Item' }).first()
		await expect(addBtn).toBeVisible({ timeout: 10_000 })
		await addBtn.click()
		// A create dialog appears.
		const dialog = page.getByRole('dialog').filter({ hasText: /Create/ })
		await expect(dialog.first()).toBeVisible({ timeout: 8_000 })
		// Cancel without persisting any data.
		await dialog.first().getByRole('button', { name: 'Cancel' }).click()
		await expect(dialog.first()).not.toBeVisible({ timeout: 5_000 })
	})

	// @e2e openspec/specs/domain-entities/spec.md#no-console-errors
	// Asserts no UNCAUGHT JS exception (pageerror) crashes the shell while
	// walking every record index page. Handled `console.error` noise from
	// empty OR collections on a seedless instance is data-dependent and not
	// asserted; an uncaught exception means the index page genuinely broke.
	test('no uncaught JS exceptions across the record index pages', async ({ page }) => {
		test.setTimeout(90_000)
		const errors: string[] = []
		page.on('pageerror', (err) => errors.push(err.message))
		// Server-routed pages via hard goto.
		for (const route of ['/medewerkers', '/berichten', '/rollen', '/zaaktypen', '/statussen']) {
			await page.goto(`${APP}${route}`)
			await dismissSupportModal(page)
			await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({ timeout: 15_000 })
		}
		// Client-routed pages via the Vue router.
		for (const route of ['/besluiten', '/documenten', '/resultaten']) {
			await spaNavigate(page, route)
			await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({ timeout: 15_000 })
		}
		expect(errors, `Uncaught JS exceptions: ${errors.join(' | ')}`).toHaveLength(0)
	})

})
