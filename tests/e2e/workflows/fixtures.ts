/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Seeded-fixture helper for the DEEP, data-dependent workflow e2e layer.
 *
 * zaakafhandelapp is a manifest-v2 (CnAppRoot) app. Its index/detail pages
 * read and write their records DIRECTLY through OpenRegister, using the
 * register slug + schema slug declared in each manifest page config
 * (register "zaakafhandelapp", schemas "zaak" / "taak" / "klant" / "status").
 * The shared object store (@conduction/nextcloud-vue useObjectStore) builds
 * every request as `/apps/openregister/api/objects/{register}/{schema}[/{id}]`
 * — see node_modules/@conduction/nextcloud-vue/src/store/useObjectStore.js
 * `_buildUrl`. The app's own ZGW `api/zrc/*` controllers are a SEPARATE,
 * legacy data path the manifest UI never touches for these pages.
 *
 * This helper therefore seeds/cleans records through the SAME OpenRegister
 * REST surface the UI uses, so a fixture row created here is byte-identical
 * to one the UI would create. Every fixture value carries a unique
 * `e2e-<runId>` prefix and afterAll() purges everything created during the
 * run.
 *
 * REAL OpenRegister API verbs used (find/findAll/saveObject under the hood):
 *   GET    api/objects/{register}/{schema}            -> list   (findAll)
 *   POST   api/objects/{register}/{schema}            -> create (saveObject)
 *   GET    api/objects/{register}/{schema}/{id}       -> read   (find)
 *   PUT    api/objects/{register}/{schema}/{id}       -> update (saveObject)
 *   DELETE api/objects/{register}/{schema}/{id}       -> delete
 *
 * Cleanup gotcha (documented, see BUGS.md in this dir): deleting a `zaak`
 * through OpenRegister fires zaakafhandelapp's ObjectDeleted event listener,
 * which currently 500s (ZGWZaakLifecycleService::find() passes the wrong OR
 * named parameter). `deleteObject` here falls back to the OR purge endpoint
 * for the zaak schema so fixture cleanup never leaks rows even while that bug
 * stands.
 */

import { request, type APIRequestContext } from '@playwright/test'
import * as path from 'path'
import { resolveBaseUrl } from '../base-url'

const STORAGE_STATE = path.resolve(__dirname, '..', '.auth', 'admin.json')
// ⚠️ No `|| 'http://localhost:8080'` fallback. THIS module writes: it creates
// and deletes OpenRegister objects over the API, so an unset environment used
// to seed fixtures into the SHARED dev container while the run reported green
// against it. See tests/e2e/base-url.ts.
const BASE = resolveBaseUrl()
const OR = '/index.php/apps/openregister/api'
const REGISTER = 'zaakafhandelapp'

/** Short, collision-resistant run id shared by every record in one run. */
export const RUN_ID = `e2e-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e4)}`

type SchemaSlug = 'zaak' | 'taak' | 'klant' | 'status'

interface SeededRecord {
	schema: SchemaSlug
	id: string
}

export class WorkflowFixtures {
	private ctx!: APIRequestContext
	private created: SeededRecord[] = []

	/** Build an authenticated OR API context from the stored admin session. */
	async init(): Promise<void> {
		this.ctx = await request.newContext({
			baseURL: BASE,
			storageState: STORAGE_STATE,
			extraHTTPHeaders: { 'OCS-APIRequest': 'true', 'Content-Type': 'application/json' },
		})
		// Fail fast with an actionable message if the OpenRegister backing the
		// manifest UI is not configured (the app expects an admin to wire a
		// register slug "zaakafhandelapp" with schemas zaak/taak/klant/status via
		// its Settings screen; without it every data page 500s — see BUGS.md).
		const reg = await this.ctx.get(`${OR}/registers/${REGISTER}`)
		if (!reg.ok()) {
			throw new Error(
				`OpenRegister register "${REGISTER}" is not configured (GET ${OR}/registers/${REGISTER} -> ${reg.status()}). `
				+ 'The zaakafhandelapp manifest UI reads/writes through OpenRegister using this register slug + schema slugs '
				+ '(zaak/taak/klant/status). Create the register and schemas (and the matching *_register / *_schema appconfig) '
				+ 'before running the workflow e2e layer. See tests/e2e/workflows/BUGS.md (Environment note).',
			)
		}
	}

	private url(schema: SchemaSlug, id?: string): string {
		return `${OR}/objects/${REGISTER}/${schema}${id ? `/${id}` : ''}`
	}

	private static idOf(body: Record<string, unknown>): string {
		const self = body['@self'] as Record<string, unknown> | undefined
		return String((self?.id ?? body.id ?? '') as string)
	}

	/** Create a record (OR saveObject) and register it for afterAll cleanup. */
	async create(schema: SchemaSlug, data: Record<string, unknown>): Promise<Record<string, unknown>> {
		const res = await this.ctx.post(this.url(schema), { data })
		if (!res.ok()) {
			throw new Error(`fixture create ${schema} failed: ${res.status()} ${await res.text()}`)
		}
		const body = await res.json()
		const id = WorkflowFixtures.idOf(body)
		if (id) this.created.push({ schema, id })
		return body
	}

	/** List records (OR findAll). Control params take the underscore prefix. */
	async list(schema: SchemaSlug, params: Record<string, string | number> = {}): Promise<Record<string, unknown>[]> {
		const qs = new URLSearchParams({ _limit: '200', ...Object.fromEntries(Object.entries(params).map(([k, v]) => [k, String(v)])) })
		const res = await this.ctx.get(`${this.url(schema)}?${qs.toString()}`)
		if (!res.ok()) throw new Error(`fixture list ${schema} failed: ${res.status()}`)
		const body = await res.json()
		return (body.results ?? []) as Record<string, unknown>[]
	}

	/** Read one record (OR find). Returns null on 404. */
	async get(schema: SchemaSlug, id: string): Promise<Record<string, unknown> | null> {
		const res = await this.ctx.get(this.url(schema, id))
		if (res.status() === 404) return null
		if (!res.ok()) throw new Error(`fixture get ${schema}/${id} failed: ${res.status()}`)
		return res.json()
	}

	/**
	 * Delete one record (OR delete). For the `zaak` and `status` schemas the
	 * app's Object event listeners 500 (BUG-1/BUG-5 in BUGS.md), so a normal
	 * delete leaks the row. We purge those by briefly renaming the schema slug
	 * so the listener's `slug === getZaakSchema()/getStatusSchema()` guard no
	 * longer matches, delete cleanly, then restore the slug. TEST-CLEANUP-only;
	 * never touches app source.
	 */
	async deleteObject(schema: SchemaSlug, id: string): Promise<boolean> {
		const res = await this.ctx.delete(this.url(schema, id))
		if (res.ok()) return true
		if (schema !== 'zaak' && schema !== 'status') return false
		return this.purgeViaSlugDetach(schema, id)
	}

	/** Resolve a schema's numeric OR id from its slug. */
	private async schemaId(slug: string): Promise<number | null> {
		const res = await this.ctx.get(`${OR}/schemas/${slug}`)
		if (!res.ok()) return null
		const body = await res.json()
		return Number(body.id) || null
	}

	/** Detach the schema slug, delete the row, restore the slug. */
	private async purgeViaSlugDetach(schema: SchemaSlug, id: string): Promise<boolean> {
		const sid = await this.schemaId(schema)
		if (!sid) return false
		const tmp = `${schema}-e2e-tmp-${Date.now().toString(36)}`
		await this.ctx.put(`${OR}/schemas/${sid}`, { data: { slug: tmp } })
		let ok = false
		try {
			const del = await this.ctx.delete(`${OR}/objects/${REGISTER}/${tmp}/${id}`)
			ok = del.ok()
		} finally {
			await this.ctx.put(`${OR}/schemas/${sid}`, { data: { slug: schema } })
		}
		return ok
	}

	/**
	 * Seed a `zaak` directly at the data layer. Returns the created zaak id;
	 * tracked for cleanup. This lets the workflow spec assert taak<->zaak
	 * LINKAGE without depending on the UI create flow.
	 *
	 * Historically this briefly detached the zaak schema slug to dodge a ZGW
	 * ObjectCreated listener that 500'd (BUG-1/3). Those hooks have since been
	 * fixed — a plain create now succeeds — and the slug-swap dance was itself
	 * fragile (the renamed slug did not always resolve in time, yielding
	 * "Schema not found: zaak-e2e-seed-*" 404s). It now delegates to the normal
	 * create path.
	 */
	async seedZaakBypassingHooks(data: Record<string, unknown>): Promise<string> {
		const obj = await this.create('zaak', data)
		return WorkflowFixtures.idOf(obj)
	}

	/** Find the runId-tagged records of a schema (for leak-free cleanup). */
	private async findRunRecords(schema: SchemaSlug): Promise<string[]> {
		const all = await this.list(schema).catch(() => [])
		return all
			.filter((o) => JSON.stringify(o).includes(RUN_ID))
			.map((o) => WorkflowFixtures.idOf(o))
			.filter(Boolean)
	}

	/**
	 * Purge everything created in this run. Deletes tracked ids first, then
	 * sweeps any runId-tagged rows the UI created (which the test did not track
	 * because they were made through the browser).
	 */
	async cleanup(): Promise<void> {
		const schemas: SchemaSlug[] = ['status', 'taak', 'zaak', 'klant']
		// Tracked ids.
		for (const rec of this.created.reverse()) {
			await this.deleteObject(rec.schema, rec.id).catch(() => undefined)
		}
		// UI-created, runId-tagged rows.
		for (const schema of schemas) {
			const ids = await this.findRunRecords(schema).catch(() => [])
			for (const id of ids) {
				await this.deleteObject(schema, id).catch(() => undefined)
			}
		}
		await this.ctx.dispose().catch(() => undefined)
	}

	get runId(): string {
		return RUN_ID
	}
}
