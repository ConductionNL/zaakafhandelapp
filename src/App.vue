<template>
	<NcContent app-name="zaakafhandelapp">
		<MainMenu />
		<RouterView />
		<SideBars />
		<Modals />
		<Dialogs />
	</NcContent>
</template>

<script>
import { NcContent } from '@nextcloud/vue'
import { RouterView } from 'vue-router'
import MainMenu from './navigation/MainMenu.vue'
import Modals from './modals/Modals.vue'
import Dialogs from './dialogs/Dialogs.vue'
import SideBars from './sidebars/SideBars.vue'

export default {
	name: 'App',
	components: {
		NcContent,
		MainMenu,
		RouterView,
		Modals,
		Dialogs,
		SideBars,
	},
}
</script>
