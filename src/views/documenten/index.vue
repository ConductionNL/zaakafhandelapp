<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZakenList/>
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.item || store.selected != 'zaken' "
				class="detailContainer"
				name="Geen document"
				description="Nog geen document geselecteerd">				
				<template #icon>
					<BriefcaseAccountOutline/>
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('zakenAdd')">
						Document toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZakenDetails v-if="store.item && store.selected === 'zaken'" :documentId="store.documentItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent,NcButton } from '@nextcloud/vue'
import MetaDataList from './list.vue'
import MetaDataDetails from './details.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'ZakenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		MetaDataList,
		MetaDataDetails,
		BriefcaseAccountOutline,
	},
	data() {
		return {
			activeMetaData: false,
			metaDataId: undefined,
		}
	}
}
</script>
