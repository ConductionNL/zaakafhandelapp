/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Shared test helpers for spec-coverage e2e tests.
 */

import type { Page } from '@playwright/test'
import { expect } from '@playwright/test'

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
