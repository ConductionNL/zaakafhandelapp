<template>
	<NcAppContent>
		<template #default>
			<div class="dashboardContainer">
				<h1>Zoekn</h1>
				Nog geen resultaten
			</div>
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'

export default {
	name: 'SearchIndex',
	components: {
		NcAppContent,
	},
}
</script>
<style>
.dashboardContainer {
    margin-inline-start: 75px;
    margin-block-start: 20px
}
</style>
