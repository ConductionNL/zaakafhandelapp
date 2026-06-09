/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Shared test helpers for spec-coverage e2e tests.
 */

import type { Page } from '@playwright/test'
import { expect } from '@playwright/test'

const APP = '/apps/zaakafhandelapp'

/**
 * Land in the SPA on a server-routed page, then navigate to `appRoute`
 * through the in-app Vue router.
 *
 * Several manifest pages (besluiten, documenten, resultaten, auditTrail,
 * features-roadmap, settings) have NO matching server-side page route in
 * `appinfo/routes.php`, so a hard `page.goto()` to them returns Nextcloud's
 * 404 page (or a 500 for /settings) and the SPA never boots. They are only
 * reachable client-side, exactly as a real user reaches them: load a routed
 * page first, then let the Vue router push the target route. This helper
 * mirrors that user journey without depending on a specific nav link being
 * present (besluiten/documenten/resultaten have no nav entry at all).
 *
 * `entryRoute` defaults to `/zaken`, which is always server-routed.
 */
export async function spaNavigate(page: Page, appRoute: string, entryRoute = '/zaken'): Promise<void> {
	await page.goto(`${APP}${entryRoute}`)
	await dismissSupportModal(page)
	// Confirm the shell mounted before we drive the client router.
	await expect(page.locator('[data-testid="cn-app-root"]')).toBeVisible({ timeout: 15_000 })
	await page.evaluate(({ app, route }) => {
		window.history.pushState({}, '', `${app}${route}`)
		window.dispatchEvent(new PopStateEvent('popstate'))
	}, { app: APP, route: appRoute })
}

/**
 * Dismiss the "Support Zaakafhandelapp" introductory modal if it appears.
 * The modal shows on first app visit per session. The Close button must be
 * scoped to the dialog to avoid strict-mode violations with other Close
 * buttons on the page (nav toggle, sidebar close, etc).
 */
export async function dismissSupportModal(page: Page): Promise<void> {
	const modal = page.getByRole('dialog', { name: 'Support Zaakafhandelapp' })
	if (await modal.isVisible({ timeout: 3_000 }).catch(() => false)) {
		// Scope the Close button to the modal dialog to avoid strict mode
		// violations with "Close navigation" and "Close sidebar" buttons.
		await modal.getByRole('button', { name: 'Close' }).click()
		await expect(modal).not.toBeVisible({ timeout: 5_000 })
	}
}
