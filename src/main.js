// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Bootstrap for zaakafhandelapp's Tier-4 manifest renderer adoption.
// Mirrors the decidesk reference (src/main.js) — see
// openspec/changes/zaakafhandelapp-manifest-v1/design.md.

import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import {
	translate as t,
	translatePlural as n,
	loadTranslations,
} from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import {
	defaultPageTypes,
	registerBuiltinDashboardWidgets,
	registerIcons,
	registerTranslations,
	buildManifest,
} from '@conduction/nextcloud-vue'
import pinia from './pinia.js'
import App from './App.vue'
import bundledManifest from './manifest.json'
import menuLayout from './menu-layout.json'
import customComponents from './customComponents.js'
import registry from './registry.js'
import appIcons from './icons.js'
import { initializeStores } from './store/store.js'
import { routesFromManifest, registerRouter } from './router/index.js'

// Library CSS — must be explicit import (webpack tree-shakes side-effect imports from aliased packages)
import '@conduction/nextcloud-vue/css/index.css'

// GridStack drives the dashboard widget grid inside CnWidgetGrid. nc-vue
// declares `gridstack` as a PEER dependency and ships none of its CSS, so the
// consumer must install it and import the stylesheet FROM THE SAME COPY —
// otherwise the JS laying the grid out and the CSS sizing it come from
// different versions. Omitting the stylesheet is the silent case: v12 sizes
// items with `width: var(--gs-column-width)`, so every widget renders 0 px
// wide with no console error at all.
import 'gridstack/dist/gridstack.min.css'

// NOTE: the `audit-trail` widget key is a library built-in (CnAuditTrailWidget,
// self-registered into the shared dashboardWidgetRegistry via the lib barrel).
// CnDetailPage resolves the manifest `type: "audit-trail"` body widgets against
// that built-in, which self-fetches register/schema/objectId from the detail
// object-context merge.

// nc-vue declares `sideEffects: ["**/*.css", …]`, which lets webpack drop the
// bare side-effect imports that register the built-in dashboard widgets. When
// that happens the widgets do not error — CnWidgetGrid renders
// "Widget not available" tiles instead. Registering explicitly at bootstrap is
// the supported escape hatch.
registerBuiltinDashboardWidgets()

// Register library-side icon set + lib translations once at bootstrap.
registerIcons(appIcons)
try {
	registerTranslations()
} catch (e) {
	// Non-fatal — lib translations fall back to English source.
	// eslint-disable-next-line no-console
	console.warn(
		'[zaakafhandelapp] registerTranslations failed; falling back to English',
		e,
	)
}

// Fire-and-forget translation load. Some Nextcloud installs (including
// this repo's standard dev container) only allow the JS/CSS allowlist
// through Apache and rewrite everything else to index.php — there's no
// route for /custom_apps/<app>/l10n/<locale>.json so the request 404s.
// `loadTranslations` rejects on 404, so wrapping the Vue mount inside
// its callback meant boot silently failed when translations couldn't
// load. Strings just fall back to their English source on miss; boot
// MUST not depend on this resolving.
function tryLoadTranslations() {
	try {
		const result = loadTranslations('zaakafhandelapp', () => {})
		if (result && typeof result.then === 'function') {
			result.then(
				() => {},
				() => {},
			)
		}
	} catch {
		// no-op
	}
}

tryLoadTranslations()

// Phase 1 of the store migration (openspec/changes/zaakafhandelapp-store-migration):
// pre-register the eleven manifest-declared object types against the lib's
// useObjectStore so manifest pages and lib sub-resource plugins (live updates,
// audit trails, files, relations) bind to a known-shape store. Synchronous
// registration (no awaits in Phase 1); fire-and-forget the returned promise to
// keep boot order deterministic and the mount unblocked, mirroring the
// translation-loading pattern above.
try {
	const result = initializeStores()
	if (result && typeof result.then === 'function') {
		result.then(
			() => {},
			(e) => {
				// eslint-disable-next-line no-console
				console.warn('[zaakafhandelapp] initializeStores failed', e)
			},
		)
	}
} catch (e) {
	// eslint-disable-next-line no-console
	console.warn('[zaakafhandelapp] initializeStores threw synchronously', e)
}

// Collect the app's manifest.d/*.json fragments — require.context is resolved
// by this app's own webpack build, so it stays app-local — then hand the base
// manifest, fragments, and menu-layout to the shared pipeline (ADR-044).
const fragmentCtx = require.context('./manifest.d/', false, /\.json$/)
const fragments = fragmentCtx
	.keys()
	.sort()
	.map((key) => fragmentCtx(key))
const mergedManifest = buildManifest(bundledManifest, fragments, menuLayout)

// vue-router 4 replaces `mode: 'hash'` + `base` with a history object that
// carries the base itself, and the router is installed per app instance
// (`app.use(router)`) rather than through a global `Vue.use(VueRouter)`.
const router = createRouter({
	history: createWebHashHistory(generateUrl('/apps/zaakafhandelapp')),
	linkActiveClass: 'active',
	routes: routesFromManifest(mergedManifest),
})
// Wire the module-level router delegate (legacy stores' `router.push`).
registerRouter(router)

// Pass shallow copies of the registry maps to CnAppRoot. The lib exports
// `defaultPageTypes` (and consumers' `customComponents`) as frozen module
// objects in some bundle shapes; cloning here yields extensible objects
// without changing the values the lib resolves at render time.
const pageTypesProp = { ...defaultPageTypes }
const customComponentsProp = { ...customComponents }
const registryProp = { ...registry }

const app = createApp(App, {
	manifest: mergedManifest,
	customComponents: customComponentsProp,
	pageTypes: pageTypesProp,
	registry: registryProp,
})

// Vue 3 has no global `Vue.mixin` / `Vue.use` — both are per-app-instance.
app.mixin({ methods: { t, n } })
app.use(pinia)
app.use(router)

// ⚠️ The host element is `#zaakafhandelapp-app`, NOT `#content`.
//
// Vue 2's `$mount()` REPLACED the matched element; Vue 3's `mount()` renders
// INSIDE it. The old `<div id="content">` in templates/index.php duplicates
// Nextcloud's own `layout.user.php` wrapper — under Vue 2 the duplication was
// invisible because the app replaced core's div, but under Vue 3 the app would
// render *inside* core's `#content` and the NcContent layout breaks. Renaming
// the host element sidesteps the question of which div wins entirely.
app.mount('#zaakafhandelapp-app')
