/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Spec-coverage e2e tests for UI — left-nav navigation.
 *
 * Exercises the real user journey of navigating between the app's pages by
 * clicking entries in the app's own left navigation (NOT the global
 * Nextcloud header). The manifest-v2 shell renders each menu entry with a
 * stable `cn-nav-entry-<id>` data-testid, which scopes every click to the
 * app nav and sidesteps the global-header "nav trap".
 *
 * For each main-section nav entry we click it and assert the destination
 * index surface mounts (the `cn-index-page` host + the page heading). This
 * is data-independent: every index page renders its chrome on a seedless
 * instance.
 *
 * @see openspec/specs/ui-search-navigation/spec.md (navigation contract)
 */

import { test, expect, type Page } from '@playwright/test'
import { dismissSupportModal, expandNav, openIndexSidebar } from './helpers'
import { APP } from '../app-path'

/**
 * The main-section nav entries and the page heading each lands on. Dashboard
 * (dashboard-type) and Search (a custom index route) are covered by their own
 * suites; this list is the entity index pages reachable from the main nav.
 */
const MAIN_NAV: Array<{ id: string, heading: string }> = [
	{ id: 'Zaken', heading: 'Cases' },
	{ id: 'Taken', heading: 'Tasks' },
	{ id: 'Klanten', heading: 'Customers' },
	{ id: 'Medewerkers', heading: 'Employees' },
	{ id: 'Contactmomenten', heading: 'Contact moments' },
	{ id: 'Berichten', heading: 'Messages' },
	// `Rollen` declares `section: "settings"` in src/manifest.json, so it is
	// not a main-section entry at all — it lives in the settings foldout.
	// `expandNav()` opens that foldout, so it is still reachable and still
	// covered here, but the list below no longer claims it is visible by
	// default (which is what SETTINGS_NAV_IDS already said).
	{ id: 'Rollen', heading: 'Roles' },
]

/**
 * Footer-section nav entries that are directly visible in the left nav body.
 * (The settings-section entries — Zaaktypen, Roles, Audit trail — live in
 * the collapsed NcAppNavigation settings slot and are present in the DOM but
 * not visible until expanded; their index pages are covered as hard-goto
 * surfaces by ui-record-views / ui-utility-pages.)
 */
const FOOTER_NAV_IDS = ['Documentation', 'FeaturesRoadmapMenu'] as const

/**
 * Settings-slot nav entries: present in the DOM but collapsed by default.
 *
 * `SettingsMenu` was removed under ADR-079 D1: it was an in-app duplicate of
 * the Nextcloud admin settings page, and because the NcAppNavigation settings
 * foldout is itself labelled "Settings" it rendered as Settings > Settings.
 * The surviving entries are the app's own domain-configuration indexes.
 */
const SETTINGS_NAV_IDS = ['Zaaktypen', 'AuditTrail'] as const

/** Open the app on a stable entry page and confirm the nav rendered. */
async function bootNav(page: Page): Promise<void> {
	await page.goto(`${APP}/#/zaken`)
	await dismissSupportModal(page)
	await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
	await expect(page.locator('[data-testid="cn-nav"]')).toBeVisible({ timeout: 10_000 })
	// Only the group holding the active route auto-expands, and the
	// settings-section entries sit in a closed foldout, so every entry outside
	// CasesGroup is rendered-but-hidden until a user opens its container.
	await expandNav(page)
}

test.describe('ui-nav-navigation — clicking left-nav entries lands on the right page', () => {

	for (const { id, heading } of MAIN_NAV) {
		// @e2e openspec/specs/ui-search-navigation/spec.md#nav-entry-navigation
		test(`nav ${id} — clicking the left-nav entry opens the ${heading} index`, async ({ page }) => {
			await bootNav(page)
			const entry = page.locator(`[data-testid="cn-nav-entry-${id}"]`)
			await expect(entry).toBeVisible({ timeout: 10_000 })
			await entry.click()
			// The destination index page mounts with its heading. The heading
			// lives in the index sidebar's header (CnIndexPage `showTitle`
			// defaults to false), and that sidebar is closed on load.
			await expect(page.locator('[data-testid="cn-index-page"]')).toBeVisible({ timeout: 10_000 })
			await openIndexSidebar(page)
			await expect(page.getByRole('heading', { name: heading, exact: true }).first()).toBeVisible({ timeout: 10_000 })
		})
	}

	for (const id of FOOTER_NAV_IDS) {
		// @e2e openspec/specs/ui-search-navigation/spec.md#nav-footer-section
		test(`nav ${id} — the footer-section entry is visible in the left nav`, async ({ page }) => {
			await bootNav(page)
			await expect(
				page.locator(`[data-testid="cn-nav-entry-${id}"]`),
			).toBeVisible({ timeout: 10_000 })
		})
	}

	// @e2e openspec/specs/ui-search-navigation/spec.md#nav-dashboard-entry
	test('nav Dashboard — clicking the Dashboard entry returns to the dashboard root', async ({ page }) => {
		await bootNav(page)
		const dash = page.locator('[data-testid="cn-nav-entry-Dashboard"]')
		await expect(dash).toBeVisible({ timeout: 10_000 })
		await dash.click()
		// The dashboard route is the app root; the page host mounts there.
		await expect(page.locator('[data-testid="cn-page"]').first()).toBeAttached({ timeout: 10_000 })
		await expect(page).toHaveURL(new RegExp(`${APP}/(#/?)?$`), { timeout: 10_000 })
	})

	// @e2e openspec/specs/ui-search-navigation/spec.md#nav-all-entries-present
	// Every manifest menu entry that declares a route renders a nav handle.
	// Main + footer entries are visible in the nav body; the settings-slot
	// entries are present in the DOM but collapsed (assert attached).
	test('all routed nav entries render in the app left nav', async ({ page }) => {
		await bootNav(page)
		const visibleIds = [
			'Dashboard', 'Zaken', 'Taken', 'Klanten', 'Medewerkers',
			'Contactmomenten', 'Berichten', 'Rollen', 'Search',
			...FOOTER_NAV_IDS,
		]
		for (const id of visibleIds) {
			await expect(
				page.locator(`[data-testid="cn-nav-entry-${id}"]`),
				`nav entry ${id} should be visible`,
			).toBeVisible({ timeout: 10_000 })
		}
		for (const id of SETTINGS_NAV_IDS) {
			await expect(
				page.locator(`[data-testid="cn-nav-entry-${id}"]`),
				`settings-slot nav entry ${id} should be present in the DOM`,
			).toBeAttached({ timeout: 10_000 })
		}
	})

})
