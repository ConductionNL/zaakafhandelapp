/**
 * SPDX-FileCopyrightText: 2026 Conduction / ZaakAfhandelApp Contributors
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Vitest configuration for ZaakAfhandelApp frontend unit tests.
 *
 * This OFFLINE suite (no Nextcloud runtime) complements the existing Jest
 * jsdom suite (the src .spec.js / .spec.ts files) with pure-logic coverage
 * Jest does not carry:
 *   • getValidISOstring (src/services/) — the date→ISO converter that doubles
 *     as a validator; exact-output assertable.
 *   • the navigation/ui Pinia store's transferData consume-once contract and
 *     view-modal / single-modal invariant.
 *
 * These need no DOM, so the environment is `node`. Vitest only collects
 * tests/vitest/**; the Jest suite (jest.config.js) is untouched.
 */

const path = require('path')

module.exports = {
	test: {
		environment: 'node',
		globals: false,
		include: ['tests/vitest/**/*.spec.{js,ts}'],
		exclude: [
			'tests/e2e/**',
			'tests/integration/**',
			'src/**',
			'node_modules/**',
		],
	},
	resolve: {
		alias: [{ find: '@', replacement: path.resolve(__dirname, 'src') }],
	},
}
