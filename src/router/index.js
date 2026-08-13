// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Manifest-driven vue-router builder. Mirrors decidesk's
// routesFromManifest pattern — each manifest page becomes one route
// (name === page.id, component === a shallow-cloned CnPageRenderer
// reference, props === true when the path declares a `:` parameter).
//
// Router instantiation lives in main.js so the merged manifest produced
// by buildManifest (ADR-044) flows into both the router and the App prop.

import { CnPageRenderer } from '@conduction/nextcloud-vue'

// Shallow-clone CnPageRenderer because the lib's barrel exports are
// non-extensible (webpack ESM module records) and both vue-router and Vue
// attach bookkeeping to a component options object. Cloning gives the router
// an extensible options object without altering the lib's internals.
const RoutePageRenderer = { ...CnPageRenderer }

/**
 * Build the vue-router config from the manifest. Each manifest page
 * becomes one route; the route's `name` IS `page.id` (per the lib's
 * manifest contract). Pages whose route declares a `:` parameter pass
 * `props: true` so the param flows in as a component prop.
 *
 * @param {object} manifest The manifest (with `pages[]`), after buildManifest merge.
 * @return {Array<object>} vue-router 4 routes config.
 */
export function routesFromManifest(manifest) {
	const routes = manifest.pages.map((page) => ({
		name: page.id,
		path: page.route,
		component: RoutePageRenderer,
		props: page.route.includes(':'),
	}))
	// Catch-all redirect to the dashboard, preserving prior router behaviour.
	//
	// ⚠️ vue-router 4 REMOVED the bare `path: '*'` wildcard. It does not throw:
	// the route simply never matches, so an unknown URL renders the app shell
	// with an empty page body and nothing at all in the console. This is the
	// v4 spelling.
	routes.push({ path: '/:pathMatch(.*)*', redirect: '/' })
	return routes
}

// ── Live router registration ─────────────────────────────────────────────
//
// Router instantiation moved to main.js under ADR-044, but the legacy
// controller-backed stores (zaken.ts, taak.js, berichten.js, …) and
// ContactMomentenForm.vue still default-import `router` from this module
// for programmatic navigation. That default export silently became
// `undefined` in the ADR-044 refactor — `router.push(...)` in those store
// actions threw at runtime, and ts-jest failed five store suites with
// TS1192 ("no default export"). The delegate below restores the contract:
// main.js registers the real router right after creating it, and the
// default export forwards `push` / `replace` to it.

let liveRouter = null

/**
 * Register the app's router instance so module-level `router.push()`
 * callers (legacy stores) delegate to the live router. Called by main.js
 * immediately after instantiation.
 *
 * @param {import('vue-router').Router} instance The created router.
 * @return {void}
 * @spec exclude Frontend plumbing — restores the module-level router contract broken by the ADR-044 refactor; no backend or spec-scenario surface.
 */
export function registerRouter(instance) {
	liveRouter = instance
}

const routerDelegate = {
	/**
	 * Forward to the live router's push; no-op with a console warning when
	 * navigation is attempted before main.js registered the instance.
	 *
	 * @param {...any} args vue-router push arguments
	 * @return {Promise<unknown>|undefined} The router's navigation promise
	 * @spec exclude Frontend plumbing — thin delegate to the live router instance; no backend or spec-scenario surface.
	 */
	push(...args) {
		if (!liveRouter) {
			console.warn(
				'[zaakafhandelapp] router.push before registerRouter()',
				args,
			)
			return undefined
		}
		return liveRouter.push(...args)
	},
	/**
	 * Forward to the live router's replace; no-op with a console warning
	 * when navigation is attempted before main.js registered the instance.
	 *
	 * @param {...any} args vue-router replace arguments
	 * @return {Promise<unknown>|undefined} The router's navigation promise
	 * @spec exclude Frontend plumbing — thin delegate to the live router instance; no backend or spec-scenario surface.
	 */
	replace(...args) {
		if (!liveRouter) {
			console.warn(
				'[zaakafhandelapp] router.replace before registerRouter()',
				args,
			)
			return undefined
		}
		return liveRouter.replace(...args)
	},
}

export default routerDelegate
