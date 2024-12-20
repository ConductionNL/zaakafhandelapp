<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<BerichtenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen bericht"
				description="Nog geen bericht geselecteerd">
				<template #icon>
					<ChatOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('editBericht')">
						Bericht aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<BerichtDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import BerichtenList from './BerichtenList.vue'
import BerichtDetails from './BerichtDetails.vue'
// eslint-disable-next-line n/no-missing-import
import ChatOutline from 'vue-material-design-icons/ChatOutline'

export default {
	name: 'BerichtenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		BerichtenList,
		BerichtDetails,
		ChatOutline,
	},
	data() {
		return {
			id: this.$route.params.id,
		}
	},
}
</script>
