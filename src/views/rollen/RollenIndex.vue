<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<RollenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.rolItem || store.selected != 'rollen' "
				class="detailContainer"
				name="Geen rol"
				description="Nog geen rol geselecteerd">
				<template #icon>
					<ChatOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('rolAdd')">
						Rol aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<RolDetails v-if="store.rolItem && store.selected === 'rollen'" :rol-id="store.rolId" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import RollenList from './RollenList.vue'
import RolDetails from './RolDetails.vue'
// eslint-disable-next-line n/no-missing-import
import ChatOutline from 'vue-material-design-icons/ChatOutline'

export default {
	name: 'RollenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		RollenList,
		RolDetails,
		ChatOutline,
	},
	data() {
		return {
			RolId: undefined,
		}
	},
}
</script>
