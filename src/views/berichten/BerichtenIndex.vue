<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<BerichtenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.berichtItem || store.selected != 'berichten' "
				class="detailContainer"
				name="Geen bericht"
				description="Nog geen bericht geselecteerd">
				<template #icon>
					<ChatOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('addBericht')">
						Bericht aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<BerichDetails v-if="store.berichtItem && store.selected === 'berichten'" :bericht-id="store.berichtId" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import BerichtenList from './BerichtenList.vue'
import BerichDetails from './BerichtDetails.vue'
// eslint-disable-next-line n/no-missing-import
import ChatOutline from 'vue-material-design-icons/ChatOutline'

export default {
	name: 'BerichtenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		BerichtenList,
		BerichDetails,
		ChatOutline,
	},
	data() {
		return {
			berichtId: undefined,
		}
	},
}
</script>
