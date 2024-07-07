<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #default>
			Dashboard hier
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent } from '@nextcloud/vue'

export default {
	name: 'Dashboard',
	components: {
		NcAppContent,
	}
}
</script>
