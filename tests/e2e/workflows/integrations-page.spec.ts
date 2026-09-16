/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * The Integrations page over integriq's connection registry
 * (adopt-connection-registry, hydra connection-registry D8 and D9).
 *
 * WHERE THE ROWS COME FROM. The rows are integriq's `app_connection` objects,
 * synced from zaakafhandelapp's `lib/Settings/connections.json`, with `app`
 * equal to `zaakafhandelapp`. Zaakafhandelapp writes no row: a configuration
 * save asks integriq to resolve again, a ZRC or BRC call reports what it met,
 * and integriq decides the status. So this spec needs integriq installed and
 * synced, and reads the rows from
 * `/apps/openregister/api/objects/integriq/app_connection?app=zaakafhandelapp`.
 *
 * `app` is a BARE filter key. The objects endpoint reads `filter[app]` as a
 * filter on nothing and answers the empty set without an error.
 *
 * WHAT A RED HERE USUALLY MEANS. An empty list in the first test means
 * integriq has not synced the declaration, or refused it whole.
 *
 * WHY THE ZRC TEST DOES NOT EXPECT CONFIGURED AFTER A SAVE. Other specs open
 * case pages that call the ZRC, and each call reports. A report outranks the
 * saved settings (contract D4 rule 4b), so "saved means Configured" holds only
 * on an instance where no ZRC call ever ran. The test drives the whole chain
 * instead: the save clears the report memory, one ZRC call to a host that
 * cannot resolve reports at once, and the row reads Error naming that host.
 *
 * Locale: nothing forces the E2E language, so statuses are read from the API
 * and rows are found by their declared titles, which are not translated.
 *
 * @e2e openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#the-page-lists-only-the-rows-of-zaakafhandelapp
 * @e2e openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#add-integration-goes-to-integriq
 * @e2e openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#a-zrc-that-does-not-answer-reads-error
 * @e2e openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#a-saved-but-unused-api-reads-not-available
 */
import type { APIRequestContext, Page } from '@playwright/test'

import { expect, test } from '@playwright/test'
import { APP } from '../app-path.ts'

/** Integriq's objects endpoint for zaakafhandelapp's connection rows. */
const CONNECTIONS_API =
	'/index.php/apps/openregister/api/objects/integriq/app_connection?app=zaakafhandelapp&_limit=50'

/** Zaakafhandelapp's configuration endpoint, the only writer of the ZGW keys. */
const CONFIGURATION_API = `${APP}/api/configuration`

/** The declared keys and titles, in declared order. */
const DECLARED = [
	{ key: 'zrc', title: 'Zaken API (ZRC)' },
	{ key: 'brc', title: 'Besluiten API (BRC)' },
	{ key: 'drc', title: 'Documenten API (DRC)' },
	{ key: 'ztc', title: 'Catalogi API (ZTC)' },
	{ key: 'orc', title: 'Overige registraties (ORC)' },
	{ key: 'klanten', title: 'Klanten API' },
	{ key: 'elastic', title: 'Elasticsearch' },
	{ key: 'mongodb', title: 'MongoDB' },
]

/** The sources the configuration saves and nothing calls. */
const UNUSED = ['drc', 'ztc', 'orc', 'klanten', 'elastic', 'mongodb']

/**
 * Zaakafhandelapp's connection rows, keyed by connection key.
 *
 * @param request An admin request context.
 * @return The rows by key.
 */
async function rowsByKey(
	request: APIRequestContext,
): Promise<Record<string, Record<string, unknown>>> {
	const res = await request.get(CONNECTIONS_API, {
		headers: { Accept: 'application/json' },
	})
	expect(res.ok(), `list integriq/app_connection -> ${res.status()}`).toBeTruthy()
	const body = await res.json()
	const byKey: Record<string, Record<string, unknown>> = {}
	for (const row of (body.results ?? []) as Record<string, unknown>[]) {
		// A row from another app here means the bare filter was dropped.
		expect(String(row.app), 'a connection row from another app').toBe(
			'zaakafhandelapp',
		)
		byKey[String(row.key)] = row
	}
	return byKey
}

/**
 * Open the Integrations page the way its menu entry does, with the preset.
 *
 * @param page The Playwright page.
 */
async function openIntegrations(page: Page): Promise<void> {
	await page.goto(`${APP}/settings/integrations?app=zaakafhandelapp`, {
		timeout: 60_000,
	})
	await expect(page.locator('.cn-index-page')).toBeVisible({ timeout: 30_000 })
}

test.describe('Integrations over the connection registry', () => {
	test("lists the eight declared connections, all of them zaakafhandelapp's", async ({
		page,
	}) => {
		const byKey = await rowsByKey(page.request)
		expect(Object.keys(byKey).sort()).toEqual(DECLARED.map((d) => d.key).sort())

		await openIntegrations(page)
		for (const { title } of DECLARED) {
			await expect(
				page.getByRole('row', {
					name: new RegExp(title.replace(/[()]/g, '\\$&'), 'i'),
				}),
			).toHaveCount(1)
		}
	})

	test('reads Not available for the six APIs nothing calls, and says why', async ({
		page,
	}) => {
		const byKey = await rowsByKey(page.request)

		for (const key of UNUSED) {
			expect(byKey[key]?.status, key).toBe('unavailable')
			expect(String(byKey[key]?.statusMessage ?? ''), key).toMatch(
				/settings are kept/i,
			)
			// No admin section writes these keys, so no row offers a link.
			expect(String(byKey[key]?.settingsUrl ?? ''), key).toBe('')
		}
	})

	test('reads Error naming the host once a ZRC call gets no answer', async ({
		page,
	}) => {
		const headers = { 'OCS-APIRequest': 'true', Accept: 'application/json' }

		// Snapshot the one key this test writes, and put the VALUE back. The
		// restore is a save too, so it clears the report memory again and the
		// next ZRC call on this instance reports what it meets at once.
		const before = await page.request.get(CONFIGURATION_API, { headers })
		expect(before.ok(), `configuration read -> ${before.status()}`).toBeTruthy()
		const previous = String((await before.json())?.zrcLocation ?? '')

		try {
			// `.invalid` never resolves (RFC 2606), so the call leaves no machine.
			const res = await page.request.post(CONFIGURATION_API, {
				headers,
				data: { zrcLocation: 'https://zrc.example.invalid/zaken/api/v1/' },
			})
			expect(res.ok(), `configuration save -> ${res.status()}`).toBeTruthy()

			// One ZRC call. Its own answer is not under test: it fails, as it did
			// before this change. What it reports is.
			await page.request.get(`${APP}/api/zrc/statussen`, {
				headers,
				failOnStatusCode: false,
			})

			// The poll reads without asserting: a throw inside `expect.poll` ends
			// the poll instead of retrying it.
			await expect
				.poll(
					async () => {
						const list = await page.request.get(CONNECTIONS_API, {
							headers: { Accept: 'application/json' },
						})
						const rows = list.ok()
							? ((await list.json()).results ?? [])
							: []
						const zrc = rows.find(
							(row: Record<string, unknown>) =>
								row.key === 'zrc' && row.app === 'zaakafhandelapp',
						)
						return `${String(zrc?.status ?? '')} ${String(zrc?.statusMessage ?? '')}`
					},
					{ timeout: 15_000 },
				)
				.toBe(
					'error The last call to the ZRC at zrc.example.invalid got no answer.',
				)
		} finally {
			await page.request.post(CONFIGURATION_API, {
				headers,
				data: { zrcLocation: previous },
			})
		}
	})

	test('sends Add integration to integriq instead of offering a form', async ({
		page,
	}) => {
		await openIntegrations(page)

		// No generic Add button: a row nothing declared has nothing to check.
		await expect(page.locator('[data-testid="cn-cta-primary"]')).toHaveCount(0)

		// The action lives in the overflow menu. English and Dutch are the two
		// catalogues this change ships, and nothing forces the E2E locale.
		await page.locator('[data-testid="cn-actions"] button').first().click()
		await Promise.all([
			page.waitForURL(
				/\/apps\/integriq\/connections\?app=zaakafhandelapp&link=1$/,
				{ timeout: 30_000 },
			),
			page
				.getByRole('menuitem', {
					name: /Add integration|Integratie toevoegen/i,
				})
				.click(),
		])
	})
})
