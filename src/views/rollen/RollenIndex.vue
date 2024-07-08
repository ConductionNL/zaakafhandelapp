<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<RollenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.item || store.selected != 'zaken' "
				class="detailContainer"
				name="Geen Rol"
				description="Nog geen rol geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('zakenAdd')">
						Rol toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<RolDetails v-if="store.item && store.selected === 'zaken'" :rol-id="store.rolItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import RollenList from './RollenList.vue'
import RolDetails from './RolDetails.vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'RollenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		RollenList,
		RolDetails,
		BriefcaseAccountOutline,
	},
	data() {
		return {
			activeMetaData: false,
			metaDataId: undefined,
		}
	},
}
</script>
