<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZakenList @zaakId="updateZaakId" />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.zaakItem || store.selected != 'zaken' "
				class="detailContainer"
				name="Geen Zaak"
				description="Nog geen zaak geselecteerd">				
				<template #icon>
					<BriefcaseAccountOutline/>
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('zaakAdd')">
						Zaak starten
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakDetails v-if="store.zaakItem && store.selected === 'zaken'" :zaak-id="zaakId" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent,NcButton } from '@nextcloud/vue'
import ZakenList from './list.vue'
import ZaakDetails from './details.vue'
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
	data() {
		return {
			zaakId: undefined,
		}
	},
	methods: {
		updateZaakId(variable) {
			this.zaakId = variable
		},
	},
}
</script>
