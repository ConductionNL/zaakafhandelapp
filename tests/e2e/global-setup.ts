/*
 * SPDX-FileCopyrightText: 2026 Zaak Afhandel App Contributors
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Playwright globalSetup — logs into Nextcloud once and persists the
 * resulting cookie jar / localStorage to `tests/e2e/.auth/admin.json`.
 * Every spec then reuses that storage state via the `use.storageState`
 * setting in playwright.config.ts, so individual tests start from an
 * authenticated session without each one paying the login cost.
 *
 * Why a real browser login (instead of POSTing to /login directly):
 * Nextcloud's login form ships a CSRF token (`requesttoken`) plus a
 * `oc_session_passphrase` cookie that must be set in the same browser
 * context. Driving the form via Playwright sidesteps having to
 * reverse-engineer the token-rotation contract, which has shifted
 * across NC 28 / 29 / 30.
 *
 * Pattern reference: ADR-030 (hydra/openspec/architecture/), mirrored
 * from launchpad's journeydoc setup (the longest-running journeydoc
 * adopter).
 */

import type { FullConfig } from '@playwright/test'

import { chromium, request } from '@playwright/test'
import { execSync } from 'child_process'
import * as fs from 'fs'
import * as path from 'path'
import { resolveBaseUrl } from './base-url.ts'

const AUTH_DIR = path.resolve(__dirname, '.auth')
const STORAGE_STATE = path.join(AUTH_DIR, 'admin.json')
const APP_ROOT = path.resolve(__dirname, '..', '..')
const BUNDLE_PATH = path.join(APP_ROOT, 'js', 'zaakafhandelapp-main.js')

/**
 * Ensure the webpack bundle exists before specs hit `/apps/zaakafhandelapp`.
 *
 * The shared `ConductionNL/.github/quality.yml` Playwright job now HAS a
 * "Build app frontend" step (`npm run build`) that runs before the spec
 * run — the note that used to stand here, saying it did not, is stale.
 *
 * Locally, the app running in the dev container is usually mounted from a
 * separate checkout, so this build only helps a checkout that serves its
 * own `js/`.
 */
function ensureBundleBuilt(): void {
	if (fs.existsSync(BUNDLE_PATH)) {
		return
	}
	// On CI this is a hard error, not something to repair.
	//
	// The shared workflow has already run its own "Build app frontend" step by
	// the time we get here, so a missing bundle means that step did not produce
	// one — and silently rebuilding turns a broken build into a green run with
	// nothing to show for it. It also makes the bundle genuinely untestable:
	// a positive control that removes the bundle to prove the specs depend on it
	// gets healed right back before the first spec runs, and the suite passes.
	// (Observed on opencatalogi: a run passed 82/82 with the bundle deleted,
	// because this function rebuilt it — the control proved nothing until it was
	// changed to truncate the file instead.)
	//
	// Locally the rebuild stays, because there it is a genuine convenience:
	// a fresh checkout has no `js/` and nothing else is going to build it.
	if (process.env.CI === 'true' || process.env.GITHUB_ACTIONS === 'true') {
		throw new Error(
			`[playwright globalSetup] bundle missing at ${BUNDLE_PATH} on CI. `
				+ 'The workflow\'s "Build app frontend" step should already have produced it — '
				+ 'check that step rather than rebuilding here, because a rebuild would hide it.',
		)
	}

	console.log(
		`[playwright globalSetup] bundle missing at ${BUNDLE_PATH}; running 'npm run build' once…`,
	)
	execSync('npm run build', { cwd: APP_ROOT, stdio: 'inherit' })
}

async function ensureNextcloudReachable(baseURL: string): Promise<void> {
	const ctx = await request.newContext()
	try {
		const res = await ctx.get(`${baseURL}/status.php`, {
			failOnStatusCode: false,
		})
		if (!res.ok()) {
			throw new Error(
				`Nextcloud status.php returned ${res.status()} at ${baseURL}. `
					+ `Make sure the docker container is running and reachable.`,
			)
		}
		const body = await res.json().catch(() => ({}))
		if (!body || body.installed !== true) {
			throw new Error(
				`Nextcloud at ${baseURL} is not installed (status.php = ${JSON.stringify(body)}).`,
			)
		}
	} finally {
		await ctx.dispose()
	}
}

export default async function globalSetup(config: FullConfig): Promise<void> {
	// No `?? 'http://localhost:8080'` fallback: that is the SHARED dev
	// container, which bind-mounts real host checkouts. See tests/e2e/base-url.ts.
	const baseURL =
		(config.projects[0]?.use?.baseURL as string | undefined) ?? resolveBaseUrl()
	const username = process.env.NC_ADMIN_USER ?? 'admin'
	const password = process.env.NC_ADMIN_PASS ?? 'admin'

	ensureBundleBuilt()
	await ensureNextcloudReachable(baseURL)
	fs.mkdirSync(AUTH_DIR, { recursive: true })

	const browser = await chromium.launch()
	const context = await browser.newContext({ baseURL })
	const page = await context.newPage()

	// Hit the login form so the CSRF token + session passphrase land in
	// the browser jar.
	await page.goto('/index.php/login')

	// If already authenticated Nextcloud redirects straight to the dashboard —
	// the login form inputs won't be present. Check before filling.
	const userInput = page.locator('input[name="user"]')
	if (await userInput.isVisible({ timeout: 3_000 }).catch(() => false)) {
		await userInput.fill(username)
		await page.locator('input[name="password"]').fill(password)
		// Wait for navigation that follows the submit click; navigationPromise
		// must be set up BEFORE the click to avoid a race condition.
		const navPromise = page.waitForURL(
			(url) => !url.pathname.includes('/login'),
			{
				timeout: 25_000,
				waitUntil: 'commit',
			},
		)
		await page.locator('button[type="submit"]').first().click()
		await navPromise
	}

	// Confirm we landed on an authenticated page (not back on login).
	const currentUrl = page.url()
	if (/\/login(\?|$|\/)/.test(currentUrl)) {
		throw new Error(
			`Login appears to have failed — still on ${currentUrl}. `
				+ `Check NC_ADMIN_USER / NC_ADMIN_PASS (defaults admin/admin).`,
		)
	}
	// No additional wait needed: waitForURL above confirmed we left the
	// login page (URL no longer contains /login), which is sufficient proof
	// that the session cookie was accepted. The storage state captured
	// immediately after includes the session cookies.

	// Persist the storage state so individual specs reuse the session.
	/*
	 * Suppress the product walkthrough (ADR-043) for automated runs, the way
	 * dossiq's global-setup already does.
	 *
	 * This became load-bearing with @conduction/nextcloud-vue 2.22.x. A
	 * `placement: "center"` welcome step used to be parked in `_pendingAutoTour`
	 * and never opened; the library now correctly starts it on any route, so the
	 * tour actually appears — and its `cn-walkthrough__dim--full` layer is a
	 * `role="dialog" aria-modal="true"` overlay that intercepts every click
	 * behind it. Specs that had never had to account for a tour started timing
	 * out, and `getByRole('dialog').first()` began resolving to the dim layer
	 * instead of the modal under test.
	 *
	 * The marker is per USER, not per test, so without it the suite is also
	 * order-dependent: whichever spec runs first wears the tour and the rest
	 * inherit a dismissed one.
	 *
	 * The sentinel is higher than any real app version, so every step's
	 * `sinceVersion` sorts below it and the tour composes to an empty step set
	 * rather than merely starting dismissed. The page is already on the instance
	 * origin after login, which is the origin storageState persists.
	 */
	try {
		await page.evaluate(() => {
			try {
				window.localStorage.setItem(
					'cn-walkthrough-seen:zaakafhandelapp',
					'999.0.0',
				)
			} catch {
				// localStorage unavailable — specs fall back to dismissing by hand.
			}
		})
	} catch {
		// Never fail setup over an optional convenience.
	}

	await context.storageState({ path: STORAGE_STATE })
	await browser.close()
}
