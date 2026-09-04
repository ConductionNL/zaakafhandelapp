/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
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
import { APP } from '../app-path.ts'
import { shootByNav, shootSurface } from './_visual-helpers.ts'

test.describe('ZaakAfhandelApp — visual baselines', () => {
	test('dashboard', async ({ page }) => {
		await shootSurface(page, `${APP}/`, 'dashboard.png')
	})

	test('cases list', async ({ page }) => {
		await shootByNav(page, `${APP}/`, 'Cases', 'cases.png')
	})
})
