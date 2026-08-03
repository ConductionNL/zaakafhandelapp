/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — Detail (`:id`) views.
 *
 * Covers the manifest-v2 `detail`-type pages, which were not exercised by
 * any prior spec-coverage suite. Each entity in src/manifest.json declares
 * a `<entity>Detail` page on the `/<plural>/:id` route, rendered generically
 * by the CnAppRoot shell.
 *
 * The detail page mounts deterministic chrome even for an id that resolves
 * to no record: the shell renders the `cn-detail-page` host and a
 * `cn-detail-page-header` whose `<h2 class="cn-detail-page__title">` carries
 * the singular entity label (e.g. "Case", "Task"). All assertions therefore
 * target that data-independent chrome rather than any record field, so the
 * suite is stable on a seedless instance. (A missing record renders the
 * header with the entity label and an empty body — it does not crash or
 * blank the shell, which is exactly what these tests assert.)
 *
 * Routing note (mirrors ui-record-views): `appinfo/routes.php` registers
 * server-side `<plural>/{id}` page routes for every detail page. BUG-1
 * (now fixed) added the missing besluiten / documenten / resultaten detail
 * routes, so all detail pages are reachable via a hard `page.goto`.
 *
 * @see openspec/specs/ui-case-views/spec.md (detail-view contract)
 * @see openspec/specs/domain-entities/spec.md
 */

import { test, expect, type Page } from '@playwright/test'
import { dismissSupportModal } from './helpers'
import { APP } from '../app-path'

// A syntactically-valid-but-nonexistent id. The detail page resolves no
// record and renders its header chrome + empty body, which is the stable,
// data-independent surface under test.
const NO_SUCH = 'e2e-no-such-record-0000'

/**
 * Assert the shared detail-view chrome on the page currently loaded.
 * `entityLabel` is the singular heading the detail header renders (e.g.
 * "Case"). The match is non-exact because some entities suffix the label
 * with the record identifier once a real record loads.
 */
async function assertDetailChrome(page: Page, entityLabel: string): Promise<void> {
	await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
	// The detail page host confirms the manifest detail page mounted.
	await expect(page.locator('[data-testid="cn-detail-page"]')).toBeVisible({ timeout: 10_000 })
	// The detail header is the deterministic, data-independent surface.
	await expect(page.locator('[data-testid="cn-detail-page-header"]')).toBeVisible({ timeout: 10_000 })
	// The header title carries the singular entity label even with no record.
	await expect(
		page.locator('[data-testid="cn-detail-page"]').getByRole('heading', { name: entityLabel }).first(),
	).toBeVisible({ timeout: 10_000 })
}

/** Server-routed detail page: reachable via a hard goto to /<plural>/:id. */
async function gotoDetail(page: Page, plural: string, entityLabel: string): Promise<void> {
	await page.goto(`${APP}/#/${plural}/${NO_SUCH}`)
	await dismissSupportModal(page)
	await assertDetailChrome(page, entityLabel)
}

test.describe('ui-detail-views — generic detail pages render shared header chrome', () => {

	// @e2e openspec/specs/domain-entities/spec.md#zaak
	test('zaken detail — case detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'zaken', 'Case')
	})

	// @e2e openspec/specs/domain-entities/spec.md#taak
	test('taken detail — task detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'taken', 'Task')
	})

	// @e2e openspec/specs/domain-entities/spec.md#klant
	test('klanten detail — customer detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'klanten', 'Customer')
	})

	// @e2e openspec/specs/domain-entities/spec.md#medewerker
	test('medewerkers detail — employee detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'medewerkers', 'Employee')
	})

	// @e2e openspec/specs/domain-entities/spec.md#bericht
	test('berichten detail — message detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'berichten', 'Message')
	})

	// @e2e openspec/specs/domain-entities/spec.md#contactmoment
	test('contactmomenten detail — contact-moment detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'contactmomenten', 'Contact moment')
	})

	// @e2e openspec/specs/domain-entities/spec.md#rol
	test('rollen detail — role detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'rollen', 'Role')
	})

	// @e2e openspec/specs/domain-entities/spec.md#zaaktype
	test('zaaktypen detail — case-type detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'zaaktypen', 'Case type')
	})

	// @e2e openspec/specs/domain-entities/spec.md#besluit
	// BUG-1 (FIXED): besluiten/{id} now has a server-side route — hard goto.
	test('besluiten detail — decision detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'besluiten', 'Decision')
	})

	// @e2e openspec/specs/domain-entities/spec.md#document
	// BUG-1 (FIXED): documenten/{id} now has a server-side route — hard goto.
	test('documenten detail — document detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'documenten', 'Document')
	})

	// @e2e openspec/specs/domain-entities/spec.md#resultaat
	// BUG-1 (FIXED): resultaten/{id} now has a server-side route — hard goto.
	test('resultaten detail — result detail page renders header chrome', async ({ page }) => {
		await gotoDetail(page, 'resultaten', 'Result')
	})

	// @e2e openspec/specs/ui-case-views/spec.md#detail-from-list
	// Drives the real user journey: open the zaken index, click the first
	// list row, and confirm the detail surface for that row opens. Guarded by
	// a seed check so the suite stays green on a seedless instance — the
	// data-independent detail chrome is already covered by the tests above.
	test('zaken detail from list — clicking a list row opens the detail surface', async ({ page }) => {
		await page.goto(`${APP}/#/zaken`)
		await dismissSupportModal(page)
		await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({ timeout: 15_000 })
		// First clickable list row in the index master list, if any seed exists.
		const firstRow = page.locator('[data-testid="cn-index-page"]').locator('tr[tabindex], [role="row"] a, .list-item, article a').first()
		const hasRow = await firstRow.isVisible({ timeout: 5_000 }).catch(() => false)
		test.skip(!hasRow, 'No seed records on the zaken list — data-independent detail chrome is covered above')
		await firstRow.click()
		// Either the URL advances to a detail route or the detail host mounts.
		await expect(page.locator('[data-testid="cn-detail-page"]')).toBeVisible({ timeout: 10_000 })
	})

	// @e2e openspec/specs/domain-entities/spec.md#no-console-errors
	// Asserts no UNCAUGHT JS exception (pageerror) crashes the shell while
	// walking every detail page with a nonexistent id. Handled `console.error`
	// noise from the empty OR fetch is data-dependent and not asserted; an
	// uncaught exception means the detail page genuinely broke.
	test('no uncaught JS exceptions across the detail pages', async ({ page }) => {
		test.setTimeout(120_000)
		const errors: string[] = []
		page.on('pageerror', (err) => errors.push(err.message))
		// Server-routed detail pages via hard goto (all have routes now).
		for (const plural of ['zaken', 'taken', 'klanten', 'medewerkers', 'berichten', 'contactmomenten', 'rollen', 'zaaktypen', 'besluiten', 'documenten', 'resultaten']) {
			await page.goto(`${APP}/#/${plural}/${NO_SUCH}`)
			await dismissSupportModal(page)
			await expect(page.locator('[data-testid="cn-detail-page"]')).toBeVisible({ timeout: 15_000 })
		}
		expect(errors, `Uncaught JS exceptions: ${errors.join(' | ')}`).toHaveLength(0)
	})

})
