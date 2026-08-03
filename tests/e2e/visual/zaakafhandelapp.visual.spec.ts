/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Visual-regression baselines for ZaakAfhandelApp's key surfaces (GAP-5).
 *
 * Run:    npx playwright test --project visual
 * Update: npx playwright test --project visual --update-snapshots
 *
 * Baselines live in tests/e2e/visual/<spec>-snapshots/ and ARE committed.
 * See _visual-helpers.ts for the platform-rendering caveat.
 */
import { test } from '@playwright/test'
import { shootSurface, shootByNav } from './_visual-helpers'
import { APP } from '../app-path'

test.describe('ZaakAfhandelApp — visual baselines', () => {
	test('dashboard', async ({ page }) => {
		await shootSurface(page, `${APP}/#/`, 'dashboard.png')
	})

	test('cases list', async ({ page }) => {
		await shootByNav(page, `${APP}/#/`, 'Cases', 'cases.png')
	})
})
