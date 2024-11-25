<script setup>
import { navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<KlantenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!klantStore.klantItem?.id || navigationStore.selected != 'klanten' "
				class="detailContainer"
				name="Geen klant"
				description="Nog geen klant geselecteerd">
				<template #icon>
					<AccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('editKlant')">
						Klant toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<KlantDetails v-if="klantStore.klantItem?.id && navigationStore.selected === 'klanten'" :klant-id="klantStore.klantItem.id" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import KlantenList from './KlantenList.vue'
import KlantDetails from './KlantDetails.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'

export default {
	name: 'KlantenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		KlantenList,
		KlantDetails,
		AccountOutline,
	},
	data() {
		return {
			activeMetaData: false,
			klantId: undefined,
		}
	},
}
</script>
