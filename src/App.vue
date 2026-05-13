<!-- SPDX-License-Identifier: EUPL-1.2 -->
<!-- Copyright (C) 2026 Conduction B.V. -->

<!--
 Zaakafhandelapp Tier-4 shell. Mounts CnAppRoot with the bundled
 manifest and the customComponents registry; provides the
 `objectSidebarState` channel so detail pages (CnDetailPage) can drive
 a single host-rendered CnObjectSidebar through the #sidebar slot.

 Modals + Dialogs hosts are kept alongside CnAppRoot — they hook into
 the navigationStore, not the manifest, and remain available as
 cross-cutting modal triggers wired to the existing pinia stores.

 @spec openspec/changes/zaakafhandelapp-manifest-v1/tasks.md#task-3.2
-->
<template>
	<div class="zaa-app-root">
		<CnAppRoot
			:manifest="manifest"
			:custom-components="customComponents"
			:page-types="pageTypes"
			app-id="zaakafhandelapp"
			:translate="translateForApp"
			:permissions="permissions">
			<template #sidebar>
				<CnObjectSidebar
					v-if="objectSidebarState.active"
					:title="objectSidebarState.title"
					:subtitle="objectSidebarState.subtitle"
					:object-type="objectSidebarState.objectType"
					:object-id="objectSidebarState.objectId"
					:register="objectSidebarState.register"
					:schema="objectSidebarState.schema"
					:hidden-tabs="objectSidebarState.hiddenTabs"
					:tabs="objectSidebarState.tabs"
					:open="objectSidebarState.open"
					@update:open="objectSidebarState.open = $event" />
			</template>
		</CnAppRoot>

		<!-- Cross-cutting modal hosts — driven by navigationStore, NOT the manifest. -->
		<Modals />
		<Dialogs />
	</div>
</template>

<script>
import Vue from 'vue'
import { translate as ncT } from '@nextcloud/l10n'
import { CnAppRoot, CnObjectSidebar } from '@conduction/nextcloud-vue'
import Modals from './modals/Modals.vue'
import Dialogs from './dialogs/Dialogs.vue'

export default {
	name: 'App',

	components: {
		CnAppRoot,
		CnObjectSidebar,
		Modals,
		Dialogs,
	},

	provide() {
		return {
			// Channel for CnDetailPage → host-rendered CnObjectSidebar.
			// Vue.observable makes the plain object reactive for Vue 2.
			objectSidebarState: this.objectSidebarState,
		}
	},

	props: {
		/**
		 * Manifest object — passed from main.js bootstrap. CnAppRoot reads
		 * `manifest.dependencies` for the dependency-check phase and
		 * `manifest.menu` for the default CnAppNav.
		 *
		 * @type {object}
		 */
		manifest: {
			type: Object,
			required: true,
		},
		/**
		 * Registry of consumer-injected components used by:
		 *   - `type: "custom"` pages (`page.component`)
		 *   - `headerComponent` / `actionsComponent` slot overrides
		 *   - `pages[].config.sidebarTabs[].component` (detail tab tabs)
		 *   - `pages[].config.sections[].component` (settings rich sections)
		 *
		 * @type {object}
		 */
		customComponents: {
			type: Object,
			default: () => ({}),
		},
		/**
		 * Page-type registry — `{ index, detail, dashboard, settings, ... }`.
		 * Wired through to descendant `CnPageRenderer` instances via
		 * provide/inject.
		 *
		 * @type {object|null}
		 */
		pageTypes: {
			type: Object,
			default: null,
		},
	},

	data() {
		return {
			objectSidebarState: Vue.observable({
				active: false,
				open: true,
				objectType: '',
				objectId: '',
				title: '',
				subtitle: '',
				register: '',
				schema: '',
				hiddenTabs: [],
				tabs: undefined,
			}),
		}
	},

	computed: {
		permissions() {
			return window.OC?.currentUser?.permissions ?? []
		},
	},

	methods: {
		/**
		 * Translate function passed down to CnAppRoot / CnAppNav /
		 * CnPageRenderer. Closes over the Nextcloud `translate` import so
		 * the lib never has to know our app id.
		 *
		 * @param {string} key Translation key.
		 * @return {string} Translated string (or the key on miss).
		 */
		translateForApp(key) {
			return ncT('zaakafhandelapp', key)
		},
	},
}
</script>
