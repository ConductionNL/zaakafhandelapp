<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<StatussenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.item || store.selected != 'zaken' "
				class="detailContainer"
				name="Geen Status"
				description="Nog geen status geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('zakenAdd')">
						Status selecteren
					</NcButton>
				</template>
			</NcEmptyContent>
			<StatusDetails v-if="store.item && store.selected === 'zaken'" :status-id="store.statusItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import StatussenList from './StatussenList.vue'
import StatusDetails from './StatusDetails.vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'StatussenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		StatussenList,
		StatusDetails,
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
