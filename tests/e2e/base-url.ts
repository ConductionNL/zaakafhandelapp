/*
 * SPDX-FileCopyrightText: 2026 Zaak Afhandel App Contributors
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Single source of truth for the Nextcloud instance the e2e suite talks to.
 *
 * WHY THIS EXISTS
 * ---------------
 * Three places resolved the target independently — `playwright.config.ts`,
 * `tests/e2e/global-setup.ts` and `tests/e2e/workflows/fixtures.ts` — and every
 * one of them fell back to `http://localhost:8080`. That is the SHARED dev
 * container, which bind-mounts real host checkouts: the fixtures module in
 * particular creates and deletes OpenRegister objects over the API, so a run
 * with no environment set wrote test data into somebody else's environment
 * while reporting green against it.
 *
 * A missing target must therefore be an ERROR, never a silent default.
 *
 * ⚠️ But the shared Conduction quality workflow exports the target as
 * `BASE_URL` — not `PLAYWRIGHT_BASE_URL`. A `PLAYWRIGHT_BASE_URL`-only
 * resolver is what broke openconnector's E2E job, which has hard-failed on
 * every run since with "PLAYWRIGHT_BASE_URL is not set". So accept CI's name
 * too, and only then throw.
 */

/**
 * Resolve the base URL of the Nextcloud instance under test.
 *
 * @throws {Error} When neither PLAYWRIGHT_BASE_URL nor BASE_URL is set.
 * @return {string} The base URL, without a trailing slash.
 */
export function resolveBaseUrl(): string {
	const url =
		process.env.PLAYWRIGHT_BASE_URL
		?? process.env.BASE_URL
		?? process.env.NEXTCLOUD_URL
		?? process.env.NC_BASE_URL

	if (!url) {
		throw new Error(
			'PLAYWRIGHT_BASE_URL (or BASE_URL) must be set to the Nextcloud instance '
				+ 'under test. There is deliberately no default: the previous fallback was '
				+ 'http://localhost:8080, the SHARED dev container, and the fixture helpers '
				+ 'write to it.',
		)
	}

	return url.replace(/\/+$/, '')
}
