<template>
	<div>
		<!-- Placeholder div for all of the sidebars-->
		<SearchSideBar v-if="$route.path.startsWith('/zoeken')" />
		<DashboardSideBar v-if="$route.path === '/'" />
	</div>
</template>

<script>
import SearchSideBar from './search/SearchSideBar.vue'
import DashboardSideBar from './dashboard/DashboardSideBar.vue'

export default {
	name: 'SideBars',
	components: {
		SearchSideBar,
		DashboardSideBar,
	},
	data() {
		return {

		}
	},
}
</script>
