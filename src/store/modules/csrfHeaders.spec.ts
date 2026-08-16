/**
 * Every mutating store call must send a CSRF token.
 *
 * WHY THIS EXISTS
 * ---------------
 * `check_csrf_callers.py` (gate-48's caller check) reported 15 unprotected
 * mutating call sites under `src/` on `development`, which blocked gate-48 for
 * every PR in this repo that touched a controller annotation. The fix adds
 * `requesttoken: getRequestToken()` to each one — a one-line change per call
 * site that nothing else in the suite would notice if it were reverted.
 *
 * A header is exactly the kind of thing a later refactor deletes silently: the
 * endpoints it is sent to all still carry `@NoCSRFRequired`, so dropping it
 * breaks NOTHING at runtime today. It only breaks the moment those annotations
 * come off — which is the whole point of having added it. So the regression has
 * to be caught here rather than by a request failing.
 *
 * WHAT IS ASSERTED
 * ----------------
 * The three call shapes the fix touches, one representative of each:
 *
 *   1. `deleteZaak`  — `fetch(url, { method: 'DELETE', headers })`, the shape
 *      shared by the 11 store `delete*()` methods gate-48 reported;
 *   2. `saveZaak`    — `fetch(url, { method, headers, body })` with `method`
 *      bound from a variable, the shape shared by the 12 `save*()` methods the
 *      checker's regex CANNOT see (it matches only a quoted literal);
 *   3. `deleteKlant` — the same DELETE shape in a plain-JS store, so the
 *      assertion is not accidentally specific to the TypeScript modules.
 *
 * Each asserts the token's VALUE, not merely that a `headers` object exists —
 * an empty or absent `requesttoken` must fail, because that is precisely the
 * state this change removed.
 *
 * SPDX-FileCopyrightText: 2026 Conduction
 * SPDX-License-Identifier: EUPL-1.2
 */
import { createPinia, setActivePinia } from 'pinia'
import { mockZaak } from '../../entities/index.js'
import { useKlantStore } from './klanten.js'
import { useZaakStore } from './zaken.js'

const TEST_TOKEN = 'test-csrf-token-value'

jest.mock('@nextcloud/auth', () => ({
	getRequestToken: () => TEST_TOKEN,
}))

jest.mock('../../router/index.js', () => ({
	__esModule: true,
	default: { push: jest.fn(), replace: jest.fn() },
}))

/**
 * The init object handed to the Nth `fetch` call.
 *
 * @param n - Zero-based index of the call.
 */
function fetchInit(n: number): RequestInit {
	return (global.fetch as jest.Mock).mock.calls[n][1] as RequestInit
}

/**
 * Headers of the Nth `fetch` call, as a plain record.
 *
 * @param n - Zero-based index of the call.
 */
function fetchHeaders(n: number): Record<string, string> {
	return (fetchInit(n).headers ?? {}) as Record<string, string>
}

describe('CSRF token on mutating store calls', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
		// One response shape that satisfies both a save (`await response.json()`
		// read as the entity) and the list refresh those methods fire off
		// afterwards (`(await response.json()).results`).
		global.fetch = jest.fn().mockResolvedValue({
			ok: true,
			status: 200,
			json: async () => ({ ...mockZaak()[0], results: [] as unknown[] }),
		}) as unknown as typeof global.fetch
	})

	afterEach(() => {
		jest.clearAllMocks()
	})

	it('sends the request token on deleteZaak (DELETE, headers-only init)', async () => {
		const store = useZaakStore()

		await store.deleteZaak({ uuid: 'a-uuid' } as never)

		expect(global.fetch).toHaveBeenCalled()
		expect(fetchInit(0).method).toBe('DELETE')
		expect(fetchHeaders(0).requesttoken).toBe(TEST_TOKEN)
	})

	it('sends the request token on saveZaak (method bound from a variable)', async () => {
		const store = useZaakStore()

		await store.saveZaak(mockZaak()[0], { setItem: false })

		expect(global.fetch).toHaveBeenCalled()
		// The fixture carries an id, so `const method = isNew ? 'POST' : 'PUT'`
		// resolves to PUT. Asserting the resolved verb is the point: the value
		// reaches `fetch` through a variable, which is exactly why
		// `check_csrf_callers.py` cannot see this call site.
		expect(fetchInit(0).method).toBe('PUT')
		expect(fetchHeaders(0).requesttoken).toBe(TEST_TOKEN)
		expect(fetchHeaders(0)['Content-Type']).toBe('application/json')
	})

	it('sends the request token on deleteKlant (same shape, plain-JS store)', async () => {
		const store = useKlantStore()

		await store.deleteKlant({ id: 'a-klant-id' })

		expect(global.fetch).toHaveBeenCalled()
		expect(fetchInit(0).method).toBe('DELETE')
		expect(fetchHeaders(0).requesttoken).toBe(TEST_TOKEN)
	})

	it('does not put a token on a read — GET stays untouched', async () => {
		const store = useZaakStore()

		await store.refreshZakenList()

		expect(fetchInit(0).method).toBe('GET')
		expect(fetchHeaders(0).requesttoken).toBeUndefined()
	})
})
