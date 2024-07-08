<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.zaakItem || store.selected != 'zaken' "
				class="detailContainer"
				name="Geen Zaak"
				description="Nog geen zaak geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('zaakAdd')">
						Zaak starten
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakDetails v-if="store.zaakItem && store.selected === 'zaken'" :zaak-id="store.zaakItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ZakenList from './ZakenList.vue'
import ZaakDetails from './ZaakDetails.vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

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
