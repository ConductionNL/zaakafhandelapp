<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ResultatenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.item || store.selected != 'zaken' "
				class="detailContainer"
				name="Geen Zaak"
				description="Nog geen zaak geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('zakenAdd')">
						Zaak starten
					</NcButton>
				</template>
			</NcEmptyContent>
			<ResultaatDetails v-if="store.item && store.selected === 'zaken'" :resultaat-id="store.resultaatItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ResultatenList from './ResultatenList.vue'
import ResultaatDetails from './ResultaatDetails.vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'ResultatenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ResultatenList,
		ResultaatDetails,
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
