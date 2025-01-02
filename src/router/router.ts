//  ===============================================
//  Please do not touch this if you don't know what you're doing
//  Even I barely know what I'm doing
//
//  If you want to learn more, ask GPT about things like nested routes
//  ===============================================

import type { RawLocation, Route } from 'vue-router'
import type { ErrorHandler } from 'vue-router/types/router.d.ts'

import { generateUrl } from '@nextcloud/router'
import Router from 'vue-router'
import Vue from 'vue'

// Pages
import Views from '../views/Views.vue'
import DashboardIndex from '../views/dashboard/DashboardIndex.vue'

// Prevent router from throwing errors when we're already on the page we're trying to go to
const originalPush = Router.prototype.push as (to: any, onComplete?: any, onAbort?: any) => Promise<Route>
Router.prototype.push = function push(to: RawLocation, onComplete?: ((route: Route) => void) | undefined, onAbort?: ErrorHandler | undefined): Promise<Route> {
	if (onComplete || onAbort) return originalPush.call(this, to, onComplete, onAbort)
	return originalPush.call(this, to).catch((err: any) => err)
}

Vue.use(Router)

const router = new Router({
	mode: 'history',

	// if index.php is in the url AND we got this far, then it's working:
	// let's keep using index.php in the url
	base: generateUrl('/apps/zaakafhandelapp'),
	linkActiveClass: 'active',

	routes: [
		{
			path: '/',
			component: Views,
			children: [
				{
					path: '',
					name: 'dashboard',
					component: DashboardIndex,
				},
				// A generic route that captures the view name and optional ID
				{
					path: ':view/:id?',
					name: 'dynamic-view',
					// Instead of directly specifying a component here,
					// we will let Views.vue handle dynamic component loading.
					// So we can either define a placeholder component here or none.
					// If we leave it empty, it means Views will have <router-view>
					// and we dynamically load inside Views.
					// But to keep it simple, let's just keep Views.vue responsible for it.
					component: {
						// A simple dummy component that just displays a <router-view />
						// This can also be done by making Views handle directly.
						// But let's assume Views.vue will have the <router-view />.
						render(h: any) { return h('div', [h('router-view')]) },
					},
					// used to watch for changes in the views modal
					// otherwise it wont reload the component
					meta: { watchParam: 'id' },
				},
			],

		},
	],
})

export default router
