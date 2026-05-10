// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Manifest-driven vue-router builder. Mirrors decidesk's
// routesFromManifest pattern — each manifest page becomes one route
// (name === page.id, component === a shallow-cloned CnPageRenderer
// reference, props === true when the path declares a `:` parameter).
//
// The legacy src/router/router.ts is replaced by this builder for new
// code paths; that file stays one cycle so any out-of-tree consumers
// keep importing successfully until the cleanup commit removes it.

import VueRouter from 'vue-router'
import Vue from 'vue'
import { generateUrl } from '@nextcloud/router'
import { CnPageRenderer } from '@conduction/nextcloud-vue'
import bundledManifest from '../manifest.json'

Vue.use(VueRouter)

// Shallow-clone CnPageRenderer because the lib's barrel exports are
// non-extensible (webpack ESM module records). Vue 2's `Vue.extend()`
// adds an internal `_Ctor` cache to the component definition; mutating
// a non-extensible export throws "Cannot add property _Ctor, object is
// not extensible". Cloning gives Vue Router an extensible
// component-options object without altering the lib's internals.
const RoutePageRenderer = { ...CnPageRenderer }

/**
 * Build the vue-router config from the manifest. Each manifest page
 * becomes one route; the route's `name` IS `page.id` (per the lib's
 * manifest contract). Pages whose route declares a `:` parameter pass
 * `props: true` so the param flows in as a component prop.
 *
 * @param {object} manifest The bundled manifest (with `pages[]`).
 * @return {Array<object>} vue-router 3 routes config.
 */
export function routesFromManifest(manifest) {
	const routes = manifest.pages.map((page) => ({
		name: page.id,
		path: page.route,
		component: RoutePageRenderer,
		props: page.route.includes(':'),
	}))
	// Catch-all redirect to dashboard, preserving prior router behaviour.
	routes.push({ path: '*', redirect: '/' })
	return routes
}

const router = new VueRouter({
	mode: 'history',
	base: generateUrl('/apps/zaakafhandelapp'),
	linkActiveClass: 'active',
	routes: routesFromManifest(bundledManifest),
})

export default router
