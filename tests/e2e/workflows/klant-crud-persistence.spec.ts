/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * DEEP, data-dependent CRUD-with-persistence e2e for Klant (customer).
 *
 * Unlike the spec-coverage suite (which asserts only that pages render), this
 * spec proves the customer feature works end-to-end against real data:
 *
 *   create (UI form, real values) -> row appears (not empty-state)
 *     -> values render in the row
 *     -> edit (row ⋯ -> Edit) -> change persists (data layer + UI)
 *     -> delete (row ⋯ -> Delete -> confirm) -> row gone (data layer + UI)
 *
 * Klant has NO ZGW event-listener hooks (only zaak/status/besluit/zio/bio do),
 * so its OpenRegister CRUD path is clean end-to-end — verified manually at
 * 201/200/204 before this spec was written. Records carry the run's unique
 * `e2e-<runId>` prefix; afterAll() purges anything left behind.
 *
 * @see openspec/specs/domain-entities/spec.md#klant
 * @see openspec/specs/ui-case-views/spec.md
 */

import { test, expect } from '@playwright/test'
import { WorkflowFixtures } from './fixtures'
import {
	openIndex,
	openCreateModal,
	fillField,
	submitModal,
	useTableView,
	rowFor,
	rowAction,
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

// One ordered journey: edit and delete act on the row the create leg made.
test.describe.configure({ mode: 'serial' })

test.describe('klant CRUD-persistence — create, read, edit, delete a customer with real data', () => {
	const naam = () => `Klant ${RUN}`
	const email = () => `${RUN}@klant.e2e.test`
	const naamEdited = () => `Klant ${RUN} EDITED`

	let klantId = ''

	test('create via UI form — a new customer row appears with the real values', async ({
		page,
	}) => {
		const index = await openIndex(page, 'klanten')

		const dialog = await openCreateModal(page)
		await fillField(dialog, 'naam', naam())
		await fillField(dialog, 'email', email())
		await fillField(dialog, 'telefoon', '0612345678')
		await submitModal(dialog)
		await expect(
			dialog.getByRole('heading', { name: /^Create /i }),
		).not.toBeVisible({ timeout: 8_000 })

		// NOT the empty-state, and the row carries the persisted values.
		await useTableView(page)
		await expect(index.getByText('No items found')).toHaveCount(0)
		const row = await rowFor(page, index, RUN)
		await expect(row).toBeVisible({ timeout: 10_000 })
		await expect(row).toContainText(naam())
		await expect(row).toContainText(email())

		// Cross-check persistence at the data layer (OR findAll).
		const rows = await fx.list('klant')
		const persisted = rows.find((r) => String(r.naam ?? '').includes(RUN))
		expect(persisted, 'customer must be persisted in OpenRegister').toBeTruthy()
		expect(String(persisted!.email)).toBe(email())
		klantId = String(
			(persisted!['@self'] as Record<string, unknown>)?.id ?? persisted!.id,
		)
		expect(klantId).toBeTruthy()
	})

	test('read — the persisted values render in the list row (data binding, not a shell)', async ({
		page,
	}) => {
		const index = await openIndex(page, 'klanten')
		const row = await rowFor(page, index, RUN)
		await expect(row).toContainText(naam())
		await expect(row).toContainText(email())
		await expect(row).toContainText('0612345678')
	})

	test('edit — changing the name persists and re-renders in the row', async ({
		page,
	}) => {
		expect(klantId, 'precondition: created in the create leg').toBeTruthy()
		const index = await openIndex(page, 'klanten')
		const row = await rowFor(page, index, RUN)

		await rowAction(page, row, 'Edit')
		const dialog = page.getByRole('dialog').first()
		await expect(dialog.getByRole('heading', { name: /Edit/i })).toBeVisible({
			timeout: 8_000,
		})
		await fillField(dialog, 'naam', naamEdited())
		await submitModal(dialog)
		await expect(dialog.getByRole('heading', { name: /Edit/i })).not.toBeVisible(
			{ timeout: 8_000 },
		)

		// Persistence at the data layer.
		await expect
			.poll(
				async () => {
					const rec = await fx.get('klant', klantId)
					return String(rec?.naam ?? '')
				},
				{ timeout: 10_000 },
			)
			.toBe(naamEdited())

		// And re-rendered in the UI row.
		const index2 = await openIndex(page, 'klanten')
		const row2 = await rowFor(page, index2, RUN)
		await expect(row2).toContainText(naamEdited())
	})

	test('delete — removing the customer takes it out of the list and the data store', async ({
		page,
	}) => {
		test.setTimeout(60_000)
		expect(klantId, 'precondition: created in the create leg').toBeTruthy()
		const index = await openIndex(page, 'klanten')
		const row = await rowFor(page, index, RUN)

		await rowAction(page, row, 'Delete')
		const confirm = page
			.getByRole('dialog')
			.filter({ hasText: /Delete item|permanently delete/i })
			.first()
		await expect(confirm).toBeVisible({ timeout: 8_000 })
		await confirm
			.getByRole('button', { name: 'Delete', exact: true })
			.first()
			.click()
		// Confirmation dialog closes once the delete is dispatched.
		await expect(confirm).not.toBeVisible({ timeout: 10_000 })

		// Gone from the data store (the authoritative assertion).
		await expect
			.poll(async () => fx.get('klant', klantId), { timeout: 15_000 })
			.toBeNull()

		// Gone from the list: the row no longer exists.
		const index2 = await openIndex(page, 'klanten')
		await useTableView(page)
		await expect(
			index2.locator('[data-testid="cn-object-row"]', { hasText: RUN }),
		).toHaveCount(0, { timeout: 10_000 })
	})
})
