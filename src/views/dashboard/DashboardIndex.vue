<template>
	<NcAppContent>
		<template #default>
			<div class="dashboardContainer">
				<h1>{{ t('zaakafhandelapp', 'Dashboard') }}</h1>
				<h2>{{ t('zaakafhandelapp', 'Workload') }}</h2>
				<NcNoteCard type="success">
					<p>{{ t('zaakafhandelapp', 'There are currently no cases without a handler') }}</p>
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
