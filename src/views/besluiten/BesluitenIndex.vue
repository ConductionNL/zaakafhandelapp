<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<BesluitenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.item || navigationStore.selected != 'zaken' "
				class="detailContainer"
				name="Geen besluit"
				description="Nog geen besluit geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('zakenAdd')">
						Besluit aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<BesluitDetails v-if="store.item && navigationStore.selected === 'zaken'" :besluit-id="store.besluitItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import BesluitenList from './BesluitenList.vue'
import BesluitDetails from './BesluitDetails.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'BesluitenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		BesluitenList,
		BesluitDetails,
		BriefcaseAccountOutline,
	},
	data() {
		return {
			zakenId: undefined,
		}
	},
}
</script>
