<template>
	<NcContent app-name="zaakafhandelapp">
		<MainMenu @open-settings="settingsOpen = true" />
		<UserSettings :open="settingsOpen" @update:open="settingsOpen = $event" />
		<RouterView />
		<SideBars />

		<!-- Object sidebar (files/notes/tags/tasks/audit trail — controlled by CnDetailPage) -->
		<CnObjectSidebar
			v-if="objectSidebarState.active"
			:object-type="objectSidebarState.objectType"
			:object-id="objectSidebarState.objectId"
			:title="objectSidebarState.title"
			:subtitle="objectSidebarState.subtitle"
			:register="objectSidebarState.register"
			:schema="objectSidebarState.schema"
			:hidden-tabs="objectSidebarState.hiddenTabs"
			:open.sync="objectSidebarState.open" />

		<Modals />
		<Dialogs />
	</NcContent>
</template>

<script>
import { NcContent } from '@nextcloud/vue'
import { CnObjectSidebar } from '@conduction/nextcloud-vue'
import { RouterView } from 'vue-router'
import MainMenu from './navigation/MainMenu.vue'
import UserSettings from './views/settings/UserSettings.vue'
import Modals from './modals/Modals.vue'
import Dialogs from './dialogs/Dialogs.vue'
import SideBars from './sidebars/SideBars.vue'

export default {
	name: 'App',
	components: {
		NcContent,
		CnObjectSidebar,
		MainMenu,
		UserSettings,
		RouterView,
		Modals,
		Dialogs,
		SideBars,
	},
	data() {
		return {
			settingsOpen: false,
			objectSidebarState: {
				active: false,
				open: true,
				objectType: '',
				objectId: '',
				title: '',
				subtitle: '',
				register: '',
				schema: '',
				hiddenTabs: [],
			},
		}
	},
	provide() {
		return {
			objectSidebarState: this.objectSidebarState,
		}
	},
}
</script>
