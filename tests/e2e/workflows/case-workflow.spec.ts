/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * DEEP case-handling WORKFLOW e2e — the high-value path for a case-handling app:
 *
 *   create a zaak (case) -> link a taak (task) to it -> change the zaak status
 *   -> assert the status persists + the linked task is reflected on the case.
 *
 * zaakafhandelapp had ZERO backend unit tests, so this workflow was never
 * exercised. Building it surfaced that the UI-driven case workflow is broken
 * end-to-end (see BUGS.md): UI case creation is rejected (BUG-3) and every zaak
 * / status mutation 500s through the systemic `find(extend:)` OR-param bug
 * (BUG-1, 5 call sites). The legs that depend on those mutations are therefore
 * test.fixme with the bug pinned.
 *
 * What IS proven green here, with real data:
 *   - a Taak (task) can be created through the UI and persists (Taken has no
 *     ZGW hook on create);
 *   - a Taak can be LINKED to a Zaak via its `zaak` field, and the linkage is
 *     real and queryable (the zaak is data-seeded past the broken create hook,
 *     because the LINKAGE MODEL — not UI case-creation — is what this asserts).
 *
 * @see ./BUGS.md
 * @see openspec/specs/zgw-case-lifecycle/spec.md
 * @see openspec/specs/domain-entities/spec.md
 */

import { test, expect } from '@playwright/test'
import { WorkflowFixtures } from './fixtures'
import {
	openIndex, openCreateModal, fillField, submitModal, useTableView, rowFor,
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

test.describe('case-workflow — task creation, case linkage, and status transition', () => {

	// GREEN: a Task is created through the UI and persists with real values.
	// @e2e openspec/specs/domain-entities/spec.md#taak
	test('task create via UI — a new task row appears and persists', async ({ page }) => {
		const index = await openIndex(page, 'taken')
		const dialog = await openCreateModal(page)
		await fillField(dialog, 'title', `Taak ${RUN}`)
		await submitModal(dialog)
		await expect(dialog.getByRole('heading', { name: /^Create/i })).not.toBeVisible({ timeout: 8_000 })

		await useTableView(page)
		await expect(index.getByText('No items found')).toHaveCount(0)
		const row = await rowFor(page, index, RUN)
		await expect(row).toContainText(`Taak ${RUN}`)

		// Persisted at the data layer.
		const rows = await fx.list('taak')
		expect(rows.find((r) => String(r.title ?? '').includes(RUN)), 'task must persist in OpenRegister').toBeTruthy()
	})

	// GREEN: the task<->case LINKAGE model. The zaak is seeded past the broken
	// ZGW create hook (BUG-1/3) so we can assert the real linkage that the case
	// workflow relies on: a task carries its parent zaak id, and the case can
	// enumerate its linked tasks. This is the data-dependent linkage proof the
	// task asks for; UI-driven case creation itself is covered (as broken) by
	// zaak-crud-persistence.spec.ts.
	// @e2e openspec/specs/zgw-case-lifecycle/spec.md#case-task-linkage
	test('case-task linkage — a task links to a case and the link is real and queryable', async ({ page }) => {
		// Seed a case at the data layer (bypassing the broken UI create hook).
		const zaakId = await fx.seedZaakBypassingHooks({
			identificatie: `ZAAK-${RUN}`,
			omschrijving: `Zaak ${RUN}`,
			status: 'open',
			archiefstatus: 'nog_te_archiveren',
		})
		expect(zaakId, 'seeded case id').toBeTruthy()

		// Link a task to that case via the task's `zaak` field (the real linkage
		// field — manifest TaakDetail and the ZaakTakenTab fetch related taken by
		// the parent zaak).
		const taak = await fx.create('taak', {
			title: `Linked taak ${RUN}`,
			status: 'open',
			priority: 'high',
			zaak: zaakId,
		})
		const taakId = String((taak['@self'] as Record<string, unknown>)?.id ?? taak.id)

		// The linkage is real: re-reading the task shows it points at the case.
		const reread = await fx.get('taak', taakId)
		expect(String(reread?.zaak ?? ''), 'task must reference its parent case').toBe(zaakId)

		// And the case can enumerate its linked tasks (findAll filtered by zaak).
		const linked = (await fx.list('taak')).filter((t) => String(t.zaak ?? '') === zaakId)
		expect(linked.length, 'case must enumerate at least its one linked task').toBeGreaterThanOrEqual(1)
		expect(linked.some((t) => String(t.title ?? '').includes(RUN))).toBe(true)

		// The linked task also renders in the Tasks list UI (real data, not a shell).
		const index = await openIndex(page, 'taken')
		const row = await rowFor(page, index, `Linked taak ${RUN}`)
		await expect(row).toContainText(`Linked taak ${RUN}`)
	})

	// FIXME (BUG-1, ZGWZaakCloseService.php:178): changing a case's status by
	// creating a `status` record 500s — `find(id:…, extend:…)` passes the wrong
	// OR named param (it is $_extend). The status never persists, so the case's
	// status does not advance. This is the core case-workflow transition.
	test.fixme('status transition — adding a status to a case persists and renders on the case', async () => {
		const zaakId = await fx.seedZaakBypassingHooks({
			identificatie: `ZAAK-${RUN}`, omschrijving: `Zaak ${RUN}`, status: 'open', archiefstatus: 'nog_te_archiveren',
		})
		// Would create a status linked to the case and assert it persists +
		// renders on the case's status tab. Blocked by BUG-1.
		await fx.create('status', { statustype: 'in behandeling', datum: '2026-01-01', zaak: zaakId })
		const statuses = (await fx.list('status')).filter((s) => String(s.zaak ?? '') === zaakId)
		expect(statuses.length).toBeGreaterThanOrEqual(1)
	})

	// FIXME (BUG-3 + BUG-1): the full UI-driven workflow — create a case from the
	// Cases screen, assign a task to it, change its status — all from the browser.
	// Blocked end-to-end: UI case creation is rejected (BUG-3) and every zaak /
	// status mutation 500s (BUG-1).
	test.fixme('full UI workflow — create case, assign task, change status entirely through the UI', async ({ page }) => {
		const index = await openIndex(page, 'zaken')
		const dialog = await openCreateModal(page)
		await fillField(dialog, 'omschrijving', `Zaak ${RUN}`)
		await submitModal(dialog)
		await expect(dialog.getByRole('heading', { name: /^Create/i })).not.toBeVisible({ timeout: 8_000 })
		await rowFor(page, index, RUN)
	})

	// FIXME (BUG-1): zaaktype-driven allowed statuses — assert a case follows its
	// zaaktype's status set. Blocked: status creation 500s, so the status set of
	// a case can never be built up through the app.
	test.fixme('zaaktype status set — a case follows its zaaktype allowed statuses', async () => {
		// Would seed a zaaktype with a status set, create a case of that type,
		// and assert only allowed statuses can be applied. Blocked by BUG-1.
		expect(true).toBe(true)
	})
})
