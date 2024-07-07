<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<BerichtenList  />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.berichtItem || store.selected != 'berichten' "
				class="detailContainer"
				name="Geen bericht"
				description="Nog geen bericht geselecteerd">				
				<template #icon>
					<ChatOutline/>
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('berichtAdd')">
						Bericht aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<BerichDetails v-if="store.berichtItem && store.selected === 'berichten'" :berichtId="store.berichtItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent,NcButton } from '@nextcloud/vue'
import BerichtenList from './list.vue'
import BerichDetails from './details.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline'

export default {
	name: 'ZakenIndex',
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
	}
}
</script>
