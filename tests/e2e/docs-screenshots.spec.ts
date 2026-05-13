/*
 * SPDX-FileCopyrightText: 2026 Zaak Afhandel App Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Documentation screenshot capture suite — zaakafhandelapp.
 *
 * This spec is *not* a regression test — it drives the Zaak Afhandel
 * App UI through the flows documented under
 * `docs/tutorials/{user,admin}/*.md` and writes a fresh PNG into
 * `docs/static/screenshots/tutorials/<track>/` for each step the
 * markdown references.
 *
 * Run manually whenever the UI changes and tutorial screenshots need
 * to be refreshed:
 *
 *     NEXTCLOUD_URL=http://localhost:8080 \
 *       npx playwright test --project docs-capture
 *
 * Excluded from the default regression run via the `docs-capture`
 * project flag in `playwright.config.ts` so PR pipelines don't
 * reshoot screenshots on every push.
 *
 * Authentication: `playwright.config.ts` wires `globalSetup` (a one-time
 * Nextcloud login → storage state) and `use.storageState`, so the
 * `page` fixture here arrives already signed in.
 *
 * Data dependency: zaakafhandelapp stores everything (zaken, taken,
 * klanten, …) in OpenRegister. On an instance with no seed data the
 * list views still render (empty state) and the *Add Item* dialog
 * still opens, so the structural screenshots below capture cleanly.
 * The flow-detail screenshots (a populated zaak, a closed rol, signed
 * besluit) need real objects; until seed data lands those steps fall
 * back to the relevant list/empty-state view, and the markdown pages
 * that reference the as-yet-uncaptured PNGs warn under
 * `onBrokenMarkdownImages: 'warn'` rather than failing the docs build.
 *
 * Pattern reference: ADR-030 (hydra/openspec/architecture/).
 */

import { test, expect, type Page } from '@playwright/test'
import * as path from 'path'
import * as fs from 'fs'

const SHOT_ROOT = path.resolve(__dirname, '..', '..', 'docs', 'static', 'screenshots', 'tutorials')
const APP = '/apps/zaakafhandelapp'

/**
 * Save a viewport screenshot under
 * `docs/static/screenshots/tutorials/<track>/<file>`.
 * Lives under `static/` so Docusaurus copies the PNG into the build
 * root — markdown image refs use `/screenshots/...` (root-absolute).
 */
async function shoot(page: Page, track: 'user' | 'admin', file: string): Promise<void> {
	const dir = path.join(SHOT_ROOT, track)
	if (!fs.existsSync(dir)) {
		fs.mkdirSync(dir, { recursive: true })
	}
	await page.screenshot({ path: path.join(dir, file), fullPage: false, type: 'png' })
}

/**
 * Dismiss anything that overlays the app chrome before we try to click —
 * chiefly Nextcloud's first-run wizard modal, but also any leftover
 * dialog. Best-effort: silently no-op when nothing's there.
 */
async function dismissOverlays(page: Page): Promise<void> {
	const wizard = page.locator('#firstrunwizard')
	if (await wizard.isVisible().catch(() => false)) {
		const close = wizard.getByRole('button', { name: /close|got it|finish|skip/i }).first()
		if (await close.isVisible().catch(() => false)) {
			await close.click().catch(() => {})
		} else {
			await page.keyboard.press('Escape').catch(() => {})
		}
		await wizard.waitFor({ state: 'hidden', timeout: 4000 }).catch(() => {})
	}
	const stray = page.locator('[role="dialog"]:not(#firstrunwizard)')
	if (await stray.first().isVisible().catch(() => false)) {
		await page.keyboard.press('Escape').catch(() => {})
		await page.waitForTimeout(300)
	}
}

/** Navigate to a Zaakafhandelapp (or absolute) route and settle. */
async function go(page: Page, route: string): Promise<void> {
	const url = route.startsWith('/apps/') ? route : `${APP}${route}`
	await page.goto(url).catch(() => { /* tolerate a 404 — caller decides */ })
	await page.waitForLoadState('networkidle').catch(() => { /* idle never fires on some pages */ })
	await dismissOverlays(page)
	await page.waitForTimeout(900)
}

/**
 * Open the create dialog on a list view (the lib's "Add Item" / "+"
 * button) if it is present, screenshot it, and close it again. Returns
 * whether the dialog appeared. The dialog body may be empty on a fresh
 * instance — the relevant schema is mapped via OpenRegister.
 */
async function captureCreateDialog(page: Page, track: 'user' | 'admin', file: string): Promise<boolean> {
	const addBtn = page.getByRole('button', { name: /Add Item|Toevoegen|\+ ?Toevoegen|Nieuwe?/i }).first()
	if (!(await addBtn.isVisible().catch(() => false))) {
		return false
	}
	await addBtn.click().catch(() => {})
	const dialog = page.locator('[role="dialog"]:not(#firstrunwizard)').first()
	await dialog.waitFor({ state: 'visible', timeout: 5000 }).catch(() => { /* no dialog */ })
	await page.waitForTimeout(400)
	await shoot(page, track, file)
	const cancel = dialog.getByRole('button', { name: /Cancel|Annuleren/i }).first()
	if (await cancel.isVisible().catch(() => false)) {
		await cancel.click().catch(() => {})
	} else {
		await page.keyboard.press('Escape').catch(() => {})
	}
	await page.waitForTimeout(300)
	return true
}

test.beforeEach(async ({ page }) => {
	page.setViewportSize({ width: 1280, height: 800 })
})

// ---------------------------------------------------------------------------
// USER TRACK — see docs/tutorials/user/
// ---------------------------------------------------------------------------

test.describe('docs: user track', () => {
	test('UN first-launch', async ({ page }) => {
		// docs/tutorials/user/01-first-launch.md
		await go(page, '/')
		await shoot(page, 'user', '01-first-launch-01.png')
		await shoot(page, 'user', '01-first-launch-02.png')
		await shoot(page, 'user', '01-first-launch-03.png')
		await go(page, '/zaken')
		await shoot(page, 'user', '01-first-launch-04.png')
		expect(page.url()).toContain('/apps/zaakafhandelapp')
	})

	test('UN create-zaak', async ({ page }) => {
		// docs/tutorials/user/02-create-zaak.md
		await go(page, '/zaken')
		const had = await captureCreateDialog(page, 'user', '02-create-zaak-01.png')
		if (had) {
			await captureCreateDialog(page, 'user', '02-create-zaak-02.png')
		}
		// Steps 3-5 (zaak detail / status / properties) need a real
		// case; the Zaken list stands in.
		await go(page, '/zaken')
		await shoot(page, 'user', '02-create-zaak-03.png')
		await go(page, '/zaaktypen')
		await shoot(page, 'user', '02-create-zaak-04.png')
		await shoot(page, 'user', '02-create-zaak-05.png')
	})

	test('UN manage-rollen', async ({ page }) => {
		// docs/tutorials/user/03-manage-rollen.md
		await go(page, '/rollen')
		await shoot(page, 'user', '03-manage-rollen-01.png')
		const had = await captureCreateDialog(page, 'user', '03-manage-rollen-02.png')
		if (!had) {
			await shoot(page, 'user', '03-manage-rollen-02.png')
		}
		await go(page, '/rollen')
		await shoot(page, 'user', '03-manage-rollen-03.png')
		await go(page, '/klanten')
		await shoot(page, 'user', '03-manage-rollen-04.png')
		await go(page, '/medewerkers')
		await shoot(page, 'user', '03-manage-rollen-05.png')
	})

	test('UN track-status', async ({ page }) => {
		// docs/tutorials/user/04-track-status.md
		await go(page, '/statussen')
		await shoot(page, 'user', '04-track-status-01.png')
		await shoot(page, 'user', '04-track-status-02.png')
		await go(page, '/zaken')
		await shoot(page, 'user', '04-track-status-03.png')
		await shoot(page, 'user', '04-track-status-04.png')
		await go(page, '/resultaten')
		await shoot(page, 'user', '04-track-status-05.png')
	})

	test('UN add-taak', async ({ page }) => {
		// docs/tutorials/user/05-add-taak.md
		await go(page, '/taken')
		await shoot(page, 'user', '05-add-taak-01.png')
		const had = await captureCreateDialog(page, 'user', '05-add-taak-02.png')
		if (!had) {
			await shoot(page, 'user', '05-add-taak-02.png')
		}
		await go(page, '/taken')
		await shoot(page, 'user', '05-add-taak-03.png')
		await shoot(page, 'user', '05-add-taak-04.png')
		// The dashboard widget shows open taken — go back to root.
		await go(page, '/')
		await shoot(page, 'user', '05-add-taak-05.png')
	})

	test('UN record-contactmoment', async ({ page }) => {
		// docs/tutorials/user/06-record-contactmoment.md
		await go(page, '/contactmomenten')
		await shoot(page, 'user', '06-record-contactmoment-01.png')
		const had = await captureCreateDialog(page, 'user', '06-record-contactmoment-02.png')
		if (!had) {
			await shoot(page, 'user', '06-record-contactmoment-02.png')
		}
		await go(page, '/contactmomenten')
		await shoot(page, 'user', '06-record-contactmoment-03.png')
		await go(page, '/berichten')
		await shoot(page, 'user', '06-record-contactmoment-04.png')
		await go(page, '/klanten')
		await shoot(page, 'user', '06-record-contactmoment-05.png')
	})

	test('UN attach-document', async ({ page }) => {
		// docs/tutorials/user/07-attach-document.md — documents live as
		// zaakinformatieobjecten linked to a zaak; the documenten list
		// is the primary surface.
		await go(page, '/documenten')
		await shoot(page, 'user', '07-attach-document-01.png')
		const had = await captureCreateDialog(page, 'user', '07-attach-document-02.png')
		if (!had) {
			await shoot(page, 'user', '07-attach-document-02.png')
		}
		await go(page, '/documenten')
		await shoot(page, 'user', '07-attach-document-03.png')
		await go(page, '/zaakinformatieobjecten')
		await shoot(page, 'user', '07-attach-document-04.png')
		await go(page, '/zaken')
		await shoot(page, 'user', '07-attach-document-05.png')
	})

	test('UN log-besluit', async ({ page }) => {
		// docs/tutorials/user/08-log-besluit.md
		await go(page, '/besluiten')
		await shoot(page, 'user', '08-log-besluit-01.png')
		const had = await captureCreateDialog(page, 'user', '08-log-besluit-02.png')
		if (!had) {
			await shoot(page, 'user', '08-log-besluit-02.png')
		}
		await go(page, '/besluiten')
		await shoot(page, 'user', '08-log-besluit-03.png')
		await go(page, '/resultaten')
		await shoot(page, 'user', '08-log-besluit-04.png')
		await go(page, '/zoeken')
		await shoot(page, 'user', '08-log-besluit-05.png')
	})
})

// ---------------------------------------------------------------------------
// ADMIN TRACK — see docs/tutorials/admin/
// ---------------------------------------------------------------------------

test.describe('docs: admin track', () => {
	test('AN configure-zaaktypen', async ({ page }) => {
		// docs/tutorials/admin/01-configure-zaaktypen.md
		await go(page, '/zaaktypen')
		await shoot(page, 'admin', '01-configure-zaaktypen-01.png')
		const had = await captureCreateDialog(page, 'admin', '01-configure-zaaktypen-02.png')
		if (!had) {
			await shoot(page, 'admin', '01-configure-zaaktypen-02.png')
		}
		await go(page, '/zaaktypen')
		await shoot(page, 'admin', '01-configure-zaaktypen-03.png')
		await shoot(page, 'admin', '01-configure-zaaktypen-04.png')
		await shoot(page, 'admin', '01-configure-zaaktypen-05.png')
	})

	test('AN manage-medewerkers', async ({ page }) => {
		// docs/tutorials/admin/02-manage-medewerkers.md
		await go(page, '/medewerkers')
		await shoot(page, 'admin', '02-manage-medewerkers-01.png')
		const had = await captureCreateDialog(page, 'admin', '02-manage-medewerkers-02.png')
		if (!had) {
			await shoot(page, 'admin', '02-manage-medewerkers-02.png')
		}
		await go(page, '/medewerkers')
		await shoot(page, 'admin', '02-manage-medewerkers-03.png')
		await go(page, '/rollen')
		await shoot(page, 'admin', '02-manage-medewerkers-04.png')
		await go(page, '/auditTrail')
		await shoot(page, 'admin', '02-manage-medewerkers-05.png')
	})

	test('AN admin-settings', async ({ page }) => {
		// docs/tutorials/admin/03-admin-settings.md — Zaakafhandelapp's
		// settings live in-app at /apps/zaakafhandelapp/settings and at
		// the Nextcloud admin section under Settings → Administration.
		await go(page, '/settings')
		await shoot(page, 'admin', '03-admin-settings-01.png')
		await page.evaluate(() => window.scrollTo(0, 0))
		await page.waitForTimeout(300)
		await shoot(page, 'admin', '03-admin-settings-02.png')
		await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight / 2))
		await page.waitForTimeout(300)
		await shoot(page, 'admin', '03-admin-settings-03.png')
		await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight))
		await page.waitForTimeout(300)
		await shoot(page, 'admin', '03-admin-settings-04.png')
		await go(page, '/apps/settings/admin/zaakafhandelapp')
		await shoot(page, 'admin', '03-admin-settings-05.png')
		expect(page.url()).toMatch(/zaakafhandelapp/)
	})
})
