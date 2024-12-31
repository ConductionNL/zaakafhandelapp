<template>
	<!-- Placeholder div for all of the vieuws-->
	<NcAppContent>
		<template #default>
			<RouterView />
			<component :is="currentViewComponent" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'
import { viewComponents } from './componentMapping.js'
import { RouterView } from 'vue-router'

export default {
	name: 'Views',
	components: {
		NcAppContent,
		RouterView,
	},
	computed: {
		currentViewComponent() {
			const viewName = this.$route.params.view
			return viewComponents[viewName] || null
		},
	},
}
</script>
