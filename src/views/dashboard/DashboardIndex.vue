<template>
	<NcAppContent>
		<template #default>
			Dashboard hier
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'

export default {
	name: 'DashboardIndex',
	components: {
		NcAppContent,
	},
}
</script>
