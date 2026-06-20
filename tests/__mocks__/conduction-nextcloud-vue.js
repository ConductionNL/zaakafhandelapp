// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Test-only stub of @conduction/nextcloud-vue.
//
// The real package transitively pulls in @nextcloud/vue + @nextcloud/auth +
// @nextcloud/browser-storage which is a chain of CJS+ESM (and JSDOM-incompatible
// globals like `structuredClone`) that jest cannot parse. The unit tests in
// this app only exercise *store factory wiring* (setBerichtItem, mockBericht,
// etc.) — they never render the lib's components — so we hand back stubs for
// every access path the import sites use.

const { defineStore } = require('pinia')

// Stub component: minimal Vue 2 SFC-shape so any consumer can `Vue.extend()`
// or pass it through `h(C)` without crashing.
const stubComponent = { name: 'CnStub', render() { return null } }

// `useObjectStore` / `createObjectStore` factory: merges plugin actions
// into a single pinia store so call sites like `objectStore.configure(...)`
// and `objectStore.registerObjectType(...)` resolve.
function createObjectStore(id, options = {}) {
	const plugins = options.plugins || []
	const merged = {
		state: () => {
			const state = { objectTypes: [], pagination: {} }
			for (const p of plugins) {
				if (typeof p?.state === 'function') Object.assign(state, p.state())
			}
			return state
		},
		getters: {},
		actions: {
			configure() {},
			registerObjectType(type) {
				if (!this.objectTypes.includes(type)) this.objectTypes.push(type)
			},
			fetchCollection() { return [] },
			getPagination() { return { total: 0, page: 1, pages: 0, limit: 25 } },
		},
	}
	for (const p of plugins) {
		Object.assign(merged.getters, p?.getters || {})
		Object.assign(merged.actions, p?.actions || {})
	}
	return defineStore(id, merged)
}

const useObjectStoreFactory = createObjectStore('cn-object-store')
const useObjectStore = (...args) => useObjectStoreFactory(...args)

const noopPlugin = name => () => ({ name, state: () => ({}), getters: {}, actions: {} })

// Real exports the app code reaches for. Anything not listed here is
// returned as a stub component / no-op function via the Proxy fallback.
const real = {
	// Components — return a minimal stub SFC.
	CnAppRoot: stubComponent,
	CnObjectSidebar: stubComponent,
	CnNoteCard: stubComponent,
	CnPageRenderer: stubComponent,

	// Bootstrap helpers used by main.js.
	defaultPageTypes: {},
	registerIcons: () => {},
	registerTranslations: () => {},

	// Store factory + plugins.
	useObjectStore,
	createObjectStore,
	createCrudStore: () => defineStore('cn-crud-stub', { state: () => ({}) }),
	createSubResourcePlugin: noopPlugin('subResource'),
	emptyPaginated: () => ({ results: [], total: 0, page: 1, pages: 0, limit: 25 }),
	auditTrailsPlugin: noopPlugin('auditTrails'),
	relationsPlugin: noopPlugin('relations'),
	filesPlugin: noopPlugin('files'),
	lifecyclePlugin: noopPlugin('lifecycle'),
	liveUpdatesPlugin: noopPlugin('liveUpdates'),
	logsPlugin: noopPlugin('logs'),
	registerMappingPlugin: noopPlugin('registerMapping'),
	selectionPlugin: noopPlugin('selection'),
	searchPlugin: noopPlugin('search'),
}

// Proxy fallback: any *other* named import (e.g. an Nc* component re-exported
// from @nextcloud/vue, a composable, or a util) resolves to a stub component
// so JSX/template references don't blow up. ESM/CJS interop checks for
// `__esModule` and `default`, so handle those too.
module.exports = new Proxy(real, {
	get(target, prop) {
		if (prop in target) return target[prop]
		if (prop === '__esModule') return true
		if (prop === 'default') return target
		if (typeof prop === 'symbol') return undefined
		// Cn* / Nc* / unknown export — return the stub component.
		return stubComponent
	},
})
