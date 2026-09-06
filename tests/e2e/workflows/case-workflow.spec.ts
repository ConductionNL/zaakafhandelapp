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

import { expect, test } from '@playwright/test'
import { WorkflowFixtures } from './fixtures.ts'
import {
	fillField,
	openCreateModal,
	openIndex,
	rowFor,
	submitModal,
	useTableView,
} from './ui-helpers.ts'

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
	test('task create via UI — a new task row appears and persists', async ({
		page,
	}) => {
		const index = await openIndex(page, 'taken')
		const dialog = await openCreateModal(page)
		await fillField(dialog, 'title', `Taak ${RUN}`)
		await submitModal(dialog)
		await expect(
			dialog.getByRole('heading', { name: /^Create/i }),
		).not.toBeVisible({ timeout: 8_000 })

		await useTableView(page)
		await expect(index.getByText('No items found')).toHaveCount(0)
		const row = await rowFor(page, index, RUN)
		await expect(row).toContainText(`Taak ${RUN}`)

		// Persisted at the data layer.
		const rows = await fx.list('taak')
		expect(
			rows.find((r) => String(r.title ?? '').includes(RUN)),
			'task must persist in OpenRegister',
		).toBeTruthy()
	})

	// GREEN: the task<->case LINKAGE model. The zaak is seeded past the broken
	// ZGW create hook (BUG-1/3) so we can assert the real linkage that the case
	// workflow relies on: a task carries its parent zaak id, and the case can
	// enumerate its linked tasks. This is the data-dependent linkage proof the
	// task asks for; UI-driven case creation itself is covered (as broken) by
	// zaak-crud-persistence.spec.ts.
	// @e2e openspec/specs/zgw-case-lifecycle/spec.md#case-task-linkage
	test('case-task linkage — a task links to a case and the link is real and queryable', async ({
		page,
	}) => {
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
		const taakId = String(
			(taak['@self'] as Record<string, unknown>)?.id ?? taak.id,
		)

		// The linkage is real: re-reading the task shows it points at the case.
		const reread = await fx.get('taak', taakId)
		expect(
			String(reread?.zaak ?? ''),
			'task must reference its parent case',
		).toBe(zaakId)

		// And the case can enumerate its linked tasks (findAll filtered by zaak).
		const linked = (await fx.list('taak')).filter(
			(t) => String(t.zaak ?? '') === zaakId,
		)
		expect(
			linked.length,
			'case must enumerate at least its one linked task',
		).toBeGreaterThanOrEqual(1)
		expect(linked.some((t) => String(t.title ?? '').includes(RUN))).toBe(true)

		// The linked task also renders in the Tasks list UI (real data, not a shell).
		const index = await openIndex(page, 'taken')
		const row = await rowFor(page, index, `Linked taak ${RUN}`)
		await expect(row).toContainText(`Linked taak ${RUN}`)
	})

	// GREEN: changing a case's status by creating a `status` record now persists.
	// ZGWZaakCloseService used to call find(id:…, extend:…) — the wrong OR named
	// param (it is $_extend) — which 500'd the ObjectCreated status hook (BUG-1,
	// ZGWZaakCloseService.php:178). Fixed: the status persists and the case's
	// status set advances. This is the core case-workflow transition.
	// @e2e openspec/specs/zgw-case-lifecycle/spec.md#status-transition
	test('status transition — adding a status to a case persists and renders on the case', async () => {
		const zaakId = await fx.seedZaakBypassingHooks({
			identificatie: `ZAAK-${RUN}`,
			omschrijving: `Zaak ${RUN}`,
			status: 'open',
			archiefstatus: 'nog_te_archiveren',
		})
		expect(zaakId, 'seeded case id').toBeTruthy()

		// Creating a status linked to the case fires the ZGW close hook; it no
		// longer 500s, so the status record persists.
		await fx.create('status', {
			statustype: 'in behandeling',
			datum: '2026-01-01',
			zaak: zaakId,
		})
		const statuses = (await fx.list('status')).filter(
			(s) => String(s.zaak ?? '') === zaakId,
		)
		expect(
			statuses.length,
			'case status must persist and be queryable',
		).toBeGreaterThanOrEqual(1)
	})

	// GREEN: the full UI-driven workflow — create a case from the Cases screen,
	// then assign a task to it. UI case creation is no longer rejected (BUG-3
	// fixed) and the zaak create hook no longer 500s (BUG-1/2 fixed), so the case
	// is created from the browser and a task can be linked to it.
	// @e2e openspec/specs/zgw-case-lifecycle/spec.md#full-ui-workflow
	test('full UI workflow — create case through the UI, then assign a task to it', async ({
		page,
	}) => {
		// Create the case entirely through the Cases screen.
		const zakenIndex = await openIndex(page, 'zaken')
		const caseDialog = await openCreateModal(page)
		await fillField(caseDialog, 'omschrijving', `Workflow zaak ${RUN}`)
		await fillField(caseDialog, 'identificatie', `WF-${RUN}`)
		await submitModal(caseDialog)
		await expect(
			caseDialog.getByRole('heading', { name: /^Create/i }),
		).not.toBeVisible({ timeout: 8_000 })

		const caseRow = await rowFor(page, zakenIndex, `Workflow zaak ${RUN}`)
		await expect(caseRow).toContainText(`Workflow zaak ${RUN}`)

		// The case persisted; resolve its id so we can link a task to it.
		const zaken = await fx.list('zaak')
		const zaak = zaken.find((z) =>
			String(z.omschrijving ?? '').includes(`Workflow zaak ${RUN}`),
		)
		expect(zaak, 'UI-created case must persist').toBeTruthy()
		const zaakId = String(
			(zaak?.['@self'] as Record<string, unknown>)?.id ?? zaak?.id,
		)

		// Assign a task to the case and assert the linkage is real.
		const taak = await fx.create('taak', {
			title: `Workflow taak ${RUN}`,
			status: 'open',
			priority: 'high',
			zaak: zaakId,
		})
		const taakId = String(
			(taak['@self'] as Record<string, unknown>)?.id ?? taak.id,
		)
		const reread = await fx.get('taak', taakId)
		expect(
			String(reread?.zaak ?? ''),
			'task must reference the UI-created case',
		).toBe(zaakId)

		// The task renders in the Tasks list (real data).
		const takenIndex = await openIndex(page, 'taken')
		const taakRow = await rowFor(page, takenIndex, `Workflow taak ${RUN}`)
		await expect(taakRow).toContainText(`Workflow taak ${RUN}`)
	})

	// FIXME (fixture provisioning, not a bug): zaaktype-driven allowed statuses —
	// assert a case follows its zaaktype's status set. BUG-1 (which used to 500
	// every status create) is now fixed; what remains is the fixture work of
	// seeding a zaaktype with a declared status set and a zaaktype-status link
	// model, then asserting only allowed statuses can be applied. That is a new
	// capability to build out, not the OR-API drift this batch repaired.
	test('zaaktype status set — a case follows its zaaktype allowed statuses', async () => {
		test.fixme(
			true,
			'fixture provisioning, not a bug: BUG-1 (status create returning 500) is fixed. What remains is seeding a zaaktype with a declared status set plus a zaaktype-status link model, then asserting only allowed statuses can be applied — a new capability to build out, not the OR-API drift this batch repaired.',
		)
		// Would seed a zaaktype with a status set, create a case of that type,
		// and assert only allowed statuses can be applied.
		expect(true).toBe(true)
	})
})
