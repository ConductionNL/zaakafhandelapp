import { defineConfig, devices } from '@playwright/test'

/**
 * Playwright config for zaakafhandelapp.
 *
 * Bootstrapped for gate-19 e2e-coverage (Playwright drives the real UI;
 * API/contract assertions belong in Newman). The shared globalSetup
 * drives the Nextcloud login once and persists the cookie jar to
 * `tests/e2e/.auth/admin.json`; every spec reuses that session via
 * `use.storageState`.
 *
 * Pattern reference: ADR-030 + larpingapp / decidesk harness.
 */
export default defineConfig({
	testDir: './tests/e2e',
	testIgnore: ['**/global-setup.ts', '**/fixtures/**'],
	timeout: 60_000,
	expect: { timeout: 15_000 },
	fullyParallel: false,
	retries: 1,
	workers: 1,
	reporter: [
		['html', { open: 'never', outputFolder: 'tests/e2e/playwright-report' }],
		['junit', { outputFile: 'tests/e2e/test-results/results.xml' }],
	],
	outputDir: 'tests/e2e/test-results',

	// Runs once before the test run, drives the NC login, persists cookies
	// to `tests/e2e/.auth/admin.json`. See `tests/e2e/global-setup.ts`.
	globalSetup: require.resolve('./tests/e2e/global-setup'),

	use: {
		baseURL: process.env.NEXTCLOUD_URL || 'http://localhost:8080',
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},

	projects: [
		{
			name: 'chromium',
			use: {
				...devices['Desktop Chrome'],
				// Pick up the authenticated storage state globalSetup wrote.
				storageState: 'tests/e2e/.auth/admin.json',
			},
		},
	],
})
