<!-- SPDX-License-Identifier: EUPL-1.2 -->
<!-- Copyright (C) 2026 Conduction B.V. -->

<!--
 Vue 3 replacement for bootstrap-vue's `<BTabs>`.

 WHY THIS EXISTS
 ---------------
 Eight detail/modal views drove their tab strips with `bootstrap-vue@2`, which
 is a **Vue 2-only** package — there is no Vue 3 release of it (the successor,
 `bootstrap-vue-next`, is a different package with a different API). Pulling in
 a second UI framework alongside `@nextcloud/vue` would also cut against the
 fleet rule that apps render with Nextcloud components.

 Neither `@nextcloud/vue@9` nor `@conduction/nextcloud-vue` ships a generic tab
 strip: the only tab component in either is `NcAppSidebarTab`, which only works
 inside an `NcAppSidebar`. So this pair reimplements the small slice of the
 BTabs/BTab contract the app actually used:

   <BTabs content-class="…" justified card class="…">
     <BTab title="…" active>…</BTab>
     <BTab :active="expr" @click="…"><template #title>…</template>…</BTab>
   </BTabs>

 A generic `CnTabs` / `CnTab` pair belongs in @conduction/nextcloud-vue rather
 than in every consumer — see the migration report.

 IMPLEMENTATION NOTE
 -------------------
 Children register themselves on mount and hand back a *render function* for
 their title rather than a plain string. That is what lets a child's `#title`
 slot (used for the closable contactmoment tabs) be rendered by the parent's
 nav strip, and it keeps a computed title string reactive: the parent reads
 `props.title` inside its own render effect, so a title change re-renders the
 nav.
-->

<template>
	<div class="zaa-tabs" :class="{ 'zaa-tabs--card': card }">
		<div class="zaa-tabs__nav"
			:class="{ 'zaa-tabs__nav--justified': justified }"
			role="tablist">
			<button v-for="tab in tabs"
				:key="tab.uid"
				type="button"
				role="tab"
				class="zaa-tabs__nav-item"
				:class="{ 'zaa-tabs__nav-item--active': isActive(tab.uid) }"
				:aria-selected="isActive(tab.uid) ? 'true' : 'false'"
				@click="tab.onActivate()">
				<component :is="tab.titleRender" />
			</button>
		</div>
		<div class="zaa-tabs__content" :class="contentClass">
			<slot />
		</div>
	</div>
</template>

<script>
import { defineComponent, provide, reactive, ref } from 'vue'
import { TABS_INJECTION_KEY } from './tabsKey.js'

export default defineComponent({
	name: 'Tabs',

	props: {
		/** Extra class applied to the panel container (BTabs `content-class`). */
		contentClass: {
			type: String,
			default: '',
		},
		/** Stretch the nav items to fill the strip (BTabs `justified`). */
		justified: {
			type: Boolean,
			default: false,
		},
		/** Card-style chrome around the strip (BTabs `card`). */
		card: {
			type: Boolean,
			default: false,
		},
	},

	setup() {
		// Registered children, in mount order — which is document order for
		// both static children and `v-for`-generated ones.
		const tabs = reactive([])
		const activeUid = ref(null)

		/**
		 * Register a child tab. The first child to register wins the initial
		 * selection unless a later one declares itself `active`.
		 *
		 * @param {object} tab Child descriptor ({ uid, titleRender, onActivate, active }).
		 * @return {void}
		 */
		function register(tab) {
			tabs.push(tab)
			if (activeUid.value === null || tab.active) {
				activeUid.value = tab.uid
			}
		}

		/**
		 * Drop a child that is being unmounted, moving the selection off it
		 * when it was the active one (the closable contactmoment tabs do this).
		 *
		 * @param {number} uid The child's instance uid.
		 * @return {void}
		 */
		function unregister(uid) {
			const index = tabs.findIndex((tab) => tab.uid === uid)
			if (index !== -1) {
				tabs.splice(index, 1)
			}
			if (activeUid.value === uid) {
				activeUid.value = tabs.length ? tabs[0].uid : null
			}
		}

		/**
		 * Make a child the visible one.
		 *
		 * @param {number} uid The child's instance uid.
		 * @return {void}
		 */
		function select(uid) {
			activeUid.value = uid
		}

		/**
		 * Whether a child is the visible one.
		 *
		 * @param {number} uid The child's instance uid.
		 * @return {boolean} True when this tab's panel should render.
		 */
		function isActive(uid) {
			return activeUid.value === uid
		}

		provide(TABS_INJECTION_KEY, { register, unregister, select, isActive })

		return { tabs, isActive }
	},
})
</script>

<style scoped>
.zaa-tabs__nav {
	display: flex;
	gap: 4px;
	border-bottom: 1px solid var(--color-border);
	overflow-x: auto;
}

.zaa-tabs__nav--justified .zaa-tabs__nav-item {
	flex: 1 1 0;
}

.zaa-tabs__nav-item {
	background: transparent;
	border: none;
	border-bottom: 2px solid transparent;
	border-radius: 0;
	color: var(--color-text-maxcontrast);
	cursor: pointer;
	font-weight: normal;
	padding: 8px 12px;
	white-space: nowrap;
}

.zaa-tabs__nav-item:hover,
.zaa-tabs__nav-item:focus-visible {
	background-color: var(--color-background-hover);
	color: var(--color-main-text);
}

.zaa-tabs__nav-item--active {
	border-bottom-color: var(--color-primary-element);
	color: var(--color-main-text);
	font-weight: bold;
}

.zaa-tabs__content {
	padding-top: 12px;
}

.zaa-tabs--card .zaa-tabs__content {
	border: 1px solid var(--color-border);
	border-top: none;
	padding: 12px;
}
</style>
