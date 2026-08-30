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
 * Routing note: `appinfo/routes.php` registers server-side page routes
 * for every manifest index page. BUG-1 (now fixed) added the missing
 * besluiten / documenten / resultaten (and statussen detail) routes, so
 * all of these index pages are now reachable via a hard `page.goto`.
 *
 * @see openspec/specs/ui-case-views/spec.md (shared index-view contract)
 * @see openspec/specs/domain-entities/spec.md
 */

import { test, expect, type Page } from '@playwright/test'
import { dismissSupportModal, openIndexSidebar } from './helpers'
import { APP } from '../app-path'

/**
 * Assert the shared index-view chrome on the page currently loaded.
 * `title` is the page heading rendered by the index page (e.g. "Employees").
 */
async function assertIndexChrome(page: Page, title: string): Promise<void> {
	await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({
		timeout: 15_000,
	})
	// The index page host confirms the manifest index page mounted.
	await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({
		timeout: 10_000,
	})
	// The primary create button is the canonical list-view action. Its label
	// is entity-specific ("Add Item", "Add Document", "Add Decision", …) —
	// match the "Add <entity>" prefix rather than a single hardcoded label so
	// every index page is covered regardless of its schema's singular name.
	await expect(page.getByRole('button', { name: /^Add /i }).first()).toBeVisible({
		timeout: 10_000,
	})
	// The view-mode chrome confirms the master list mounted. CnActionsBar
	// renders the Cards/Table segmented control as `aria-pressed` buttons
	// inside a `role="group"` — it used to be an NcCheckboxRadioSwitch radio
	// group, which is what this suite was written against.
	await expect(page.getByRole('button', { name: 'Cards' }).first()).toBeVisible({
		timeout: 10_000,
	})
	// The index sidebar is closed on load (see openIndexSidebar). The page
	// heading lives in its header, so open it and assert the heading there —
	// that is where CnIndexPage puts the title while `showTitle` is false.
	await openIndexSidebar(page)
	await expect(
		page.getByRole('heading', { name: title, exact: true }).first(),
	).toBeVisible({ timeout: 10_000 })
}

/** Server-routed index page: reachable via a hard goto. */
async function gotoIndex(page: Page, route: string, title: string): Promise<void> {
	await page.goto(`${APP}/#${route}`)
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
	// BUG-1 (FIXED): besluiten now has a server-side page route — hard goto.
	test('besluiten — Decisions index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/besluiten', 'Decisions')
	})

	// @e2e openspec/specs/domain-entities/spec.md#document
	// BUG-1 (FIXED): documenten now has a server-side page route — hard goto.
	test('documenten — Documents index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/documenten', 'Documents')
	})

	// @e2e openspec/specs/domain-entities/spec.md#resultaat
	// BUG-1 (FIXED): resultaten now has a server-side page route — hard goto.
	test('resultaten — Results index renders list chrome', async ({ page }) => {
		await gotoIndex(page, '/resultaten', 'Results')
	})

	// @e2e openspec/specs/ui-case-views/spec.md#table-view-mode
	// The Cards/Table control is CnActionsBar's segmented toggle. It is no
	// longer an NcCheckboxRadioSwitch radio group: it renders plain buttons
	// carrying `aria-pressed` inside a `role="group" aria-label="View mode"`.
	// (Worth raising in nc-vue — a mutually-exclusive segmented control is an
	// ARIA radiogroup, not a set of independent toggle buttons — but the
	// semantics belong there, not in this app's e2e suite.)
	test('view-mode toggle — the Table segment switches the medewerkers list to table mode', async ({
		page,
	}) => {
		await page.goto(`${APP}/#/medewerkers`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({
			timeout: 15_000,
		})
		const viewToggle = page.getByRole('group', { name: 'View mode' }).first()
		const cardsBtn = viewToggle.getByRole('button', { name: 'Cards' }).first()
		const tableBtn = viewToggle.getByRole('button', { name: 'Table' }).first()
		await expect(tableBtn).toBeVisible({ timeout: 10_000 })
		// Switch away and back so the assertion proves the control actually
		// drives the mode rather than merely observing the default.
		await cardsBtn.click()
		await expect(cardsBtn).toHaveAttribute('aria-pressed', 'true')
		await tableBtn.click()
		await expect(tableBtn).toHaveAttribute('aria-pressed', 'true')
		// The list chrome survives the mode switch.
		await expect(cardsBtn).toBeVisible()
	})

	// @e2e openspec/specs/ui-modals/spec.md#opening-a-create-modal
	test('rollen create modal — Add button opens the create dialog and cancels cleanly', async ({
		page,
	}) => {
		await page.goto(`${APP}/#/rollen`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({
			timeout: 15_000,
		})
		const addBtn = page.getByRole('button', { name: /^Add /i }).first()
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
	test('no uncaught JS exceptions across the record index pages', async ({
		page,
	}) => {
		test.setTimeout(90_000)
		const errors: string[] = []
		page.on('pageerror', (err) => errors.push(err.message))
		// Server-routed pages via hard goto (all index pages have routes now).
		for (const route of [
			'/medewerkers',
			'/berichten',
			'/rollen',
			'/zaaktypen',
			'/statussen',
			'/besluiten',
			'/documenten',
			'/resultaten',
		]) {
			await page.goto(`${APP}/#${route}`)
			await dismissSupportModal(page)
			await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({
				timeout: 15_000,
			})
		}
		expect(errors, `Uncaught JS exceptions: ${errors.join(' | ')}`).toHaveLength(
			0,
		)
	})
})
