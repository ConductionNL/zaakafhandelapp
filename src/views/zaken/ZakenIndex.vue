<script setup>
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen Zaak"
				description="Nog geen zaak geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="zaakStore.setZaakItem(null); navigationStore.setModal('zaakForm')">
						Zaak starten
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ZakenList from './ZakenList.vue'
import ZaakDetails from './ZaakDetails.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'ZakenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ZakenList,
		ZaakDetails,
		BriefcaseAccountOutline,
	},
	data() {
		return {
			id: this.$route.params.id,
		}
	},
}
</script>
