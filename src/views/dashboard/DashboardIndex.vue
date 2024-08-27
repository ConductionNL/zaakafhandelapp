<template>
	<NcAppContent>
		<template #default>
			<div class="dashboardContainer">
				<h1>Dashboard</h1>
				<h2>Werkvooraad</h2>
				<NcNoteCard type="success">
					<p>Er zijn op dit moment geen zaken zonder behandelaar</p>
				</NcNoteCard>
			</div>
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcNoteCard } from '@nextcloud/vue'

export default {
	name: 'DashboardIndex',
	components: {
		NcAppContent,
		NcNoteCard,
	},
}
</script>
<style>
.dashboardContainer {
    margin-inline-start: 75px;
    margin-block-start: 20px
}
</style>
