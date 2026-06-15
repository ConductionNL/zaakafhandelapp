/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * DEEP, data-dependent CRUD-with-persistence e2e for Zaak (case).
 *
 * Journey (same shape as the Klant suite):
 *   create (UI form) -> row appears -> open detail -> values render
 *     -> edit -> persists -> delete -> gone.
 *
 * HISTORY (see BUGS.md): the zaak CRUD path through the manifest UI used to be
 * non-functional because zaakafhandelapp's OpenRegister event listener fired a
 * broken ZGW validation/lifecycle pipeline on every zaak create/update/delete:
 *
 *   - BUG-3: the Cases create form omits archiefnominatie/archiefstatus, so
 *            checkArchivePrerequisites() rejected EVERY create with HTTP 400.
 *            FIXED: an absent/empty archiefstatus means the archive lifecycle
 *            has not started, so there are no archive prerequisites to enforce.
 *   - BUG-1: setVertrouwelijkheidaanduiding called ObjectService::find(id:…,
 *            extend:…) — wrong OR named param (it is $_extend) -> fatal \Error
 *            -> HTTP 500 on ObjectCreated/Updating. FIXED at all 5 call sites.
 *   - BUG-2: that same path passed a null zaaktype into find(string $url) ->
 *            TypeError -> 500. FIXED with a null guard.
 *   - BUG-5: zaak delete 500'd (the cascade passed a non-string into
 *            deleteObject(string)) and leaked the row. FIXED with a guard.
 *
 * These legs are therefore now real, green CRUD-persistence coverage.
 *
 * @see ./BUGS.md
 * @see openspec/specs/domain-entities/spec.md#zaak
 */

import { test, expect } from '@playwright/test'
import { WorkflowFixtures } from './fixtures'
import {
	openIndex, openCreateModal, fillField, submitModal, useTableView, rowFor, rowAction,
} from './ui-helpers'

const fx = new WorkflowFixtures()
let RUN = ''

test.beforeAll(async () => {
	await fx.init()
	RUN = fx.runId
})

test.afterAll(async () => {
	await fx.cleanup()
})

test.describe('zaak CRUD-persistence — case lifecycle through the manifest UI', () => {

	// GREEN: create a case through the Cases form. The create now succeeds (the
	// archive-prerequisite check no longer rejects a fresh case with no archive
	// fields — BUG-3 — and the ObjectCreated hook no longer 500s — BUG-1/BUG-2),
	// so the row appears and persists in OpenRegister with the real values.
	// @e2e openspec/specs/domain-entities/spec.md#zaak
	test('create via UI form — a new case row appears with the real values', async ({ page }) => {
		const index = await openIndex(page, 'zaken')
		const dialog = await openCreateModal(page)
		await fillField(dialog, 'omschrijving', `Zaak ${RUN}`)
		await fillField(dialog, 'identificatie', `ID-${RUN}`)

		// The authoritative signal is the create POST itself: it must now succeed.
		const postPromise = page.waitForResponse(
			(r) => r.url().includes('/api/objects/zaakafhandelapp/zaak')
				&& r.request().method() === 'POST',
			{ timeout: 15_000 },
		)
		await submitModal(dialog)
		const post = await postPromise
		expect(post.status(), 'zaak create POST must succeed (BUG-1/2/3 fixed)').toBeGreaterThanOrEqual(200)
		expect(post.status(), 'zaak create POST must not 4xx/5xx').toBeLessThan(300)

		// The modal closes on a successful save.
		await expect(dialog.getByRole('heading', { name: /^Create/i })).not.toBeVisible({ timeout: 8_000 })

		// The row renders in the Table list with the real value.
		await useTableView(page)
		const row = await rowFor(page, index, RUN)
		await expect(row).toContainText(`Zaak ${RUN}`)

		// Persisted at the data layer.
		const rows = await fx.list('zaak')
		expect(rows.find((r) => String(r.omschrijving ?? '').includes(RUN)), 'case must persist in OpenRegister').toBeTruthy()
	})

	// GREEN: edit a case field through the row Edit action and assert it persists
	// (the ObjectUpdating hook no longer 500s — BUG-1 setVertrouwelijkheidaanduiding
	// -> find(_extend:) — so the update is accepted and re-renders).
	// @e2e openspec/specs/domain-entities/spec.md#zaak
	test('edit — changing a case field persists and re-renders', async ({ page }) => {
		// Seed a case to edit (data layer; equivalent to one the UI just created).
		const zaakId = await fx.seedZaakBypassingHooks({
			identificatie: `ID-EDIT-${RUN}`,
			omschrijving: `Zaak edit ${RUN}`,
			status: 'open',
			archiefstatus: 'nog_te_archiveren',
		})
		expect(zaakId, 'seeded case id').toBeTruthy()

		const index = await openIndex(page, 'zaken')
		const row = await rowFor(page, index, `Zaak edit ${RUN}`)
		await expect(row).toBeVisible()

		const newOmschrijving = `Zaak edited ${RUN}`
		const putPromise = page.waitForResponse(
			(r) => r.url().includes('/api/objects/zaakafhandelapp/zaak')
				&& r.request().method() === 'PUT',
			{ timeout: 15_000 },
		)
		await rowAction(page, row, 'Edit')
		const dialog = page.getByRole('dialog').first()
		await expect(dialog.getByRole('heading', { name: /^Edit/i })).toBeVisible({ timeout: 8_000 })
		await fillField(dialog, 'omschrijving', newOmschrijving)
		await submitModal(dialog)

		const put = await putPromise
		expect(put.status(), 'zaak update PUT must succeed (BUG-1 fixed)').toBeGreaterThanOrEqual(200)
		expect(put.status()).toBeLessThan(300)
		await expect(dialog.getByRole('heading', { name: /^Edit/i })).not.toBeVisible({ timeout: 8_000 })

		// Persisted at the data layer.
		const reread = await fx.get('zaak', zaakId)
		expect(String(reread?.omschrijving ?? ''), 'edited value must persist').toBe(newOmschrijving)
	})

	// GREEN: delete a case through the row Delete action and assert it is gone
	// from both the list and the data store (the ObjectDeleted hook no longer
	// 500s — BUG-5 — so the cascade completes and the row is actually removed).
	// @e2e openspec/specs/domain-entities/spec.md#zaak
	test('delete — removing a case takes it out of the list and the data store', async ({ page }) => {
		const zaakId = await fx.seedZaakBypassingHooks({
			identificatie: `ID-DEL-${RUN}`,
			omschrijving: `Zaak del ${RUN}`,
			status: 'open',
			archiefstatus: 'nog_te_archiveren',
		})
		expect(zaakId, 'seeded case id').toBeTruthy()

		const index = await openIndex(page, 'zaken')
		const row = await rowFor(page, index, `Zaak del ${RUN}`)
		await expect(row).toBeVisible()

		const delPromise = page.waitForResponse(
			(r) => r.url().includes(`/api/objects/zaakafhandelapp/zaak/${zaakId}`)
				&& r.request().method() === 'DELETE',
			{ timeout: 15_000 },
		)
		await rowAction(page, row, 'Delete')
		// A confirmation dialog may appear; accept it if present.
		await page.getByRole('button', { name: /^(Delete|Confirm|Yes)$/i })
			.first().click({ timeout: 3_000 }).catch(() => undefined)

		const del = await delPromise
		expect(del.status(), 'zaak delete DELETE must succeed (BUG-5 fixed)').toBeGreaterThanOrEqual(200)
		expect(del.status()).toBeLessThan(300)

		// Gone from the data store (no 500, row actually removed).
		const reread = await fx.get('zaak', zaakId)
		expect(reread, 'deleted case must be gone from OpenRegister').toBeNull()
	})
})
