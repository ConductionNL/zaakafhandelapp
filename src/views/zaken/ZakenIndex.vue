<script setup>
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!zaakStore.zaakId || navigationStore.selected != 'zaken' "
				class="detailContainer"
				name="Geen Zaak"
				description="Nog geen zaak geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('addZaak')">
						Zaak starten
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakDetails v-if="zaakStore.zaakId && navigationStore.selected === 'zaken'" :zaak-id="zaakStore.zaakId" />
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
}
</script>
