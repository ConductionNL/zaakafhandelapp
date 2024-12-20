<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<MedewerkerList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen medewerker"
				description="Nog geen medewerker geselecteerd">
				<template #icon>
					<AccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('editMedewerker')">
						Medewerker toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<MedewerkerDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import MedewerkerList from './MedewerkerList.vue'
import MedewerkerDetails from './MedewerkerDetails.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'

export default {
	name: 'MedewerkerIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		MedewerkerList,
		MedewerkerDetails,
		AccountOutline,
	},
	data() {
		return {
			id: this.$route.params.id,
		}
	},
}
</script>
