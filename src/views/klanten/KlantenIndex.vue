<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<KlantenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.klantItem || !store.klantItem.id || store.selected != 'klanten' "
				class="detailContainer"
				name="Geen klant"
				description="Nog geen klant geselecteerd">
				<template #icon>
					<AccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('addKlant')">
						Klant toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<KlantDetails v-if="store.klantItem && store.klantItem.id && store.selected === 'klanten'" :klant-id="store.klantId" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import KlantenList from './KlantenList.vue'
import KlantDetails from './KlantDetails.vue'
// eslint-disable-next-line n/no-missing-import
import AccountOutline from 'vue-material-design-icons/AccountOutline'

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
