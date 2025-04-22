<template>
	<NcAppContent>
		<template #list>
			<KlantenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen klant"
				description="Nog geen klant geselecteerd">
				<template #icon>
					<AccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="objectStore.clearActiveObject('klant'); navigationStore.setModal('editKlant')">
						Klant toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<KlantDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script lang="ts">
// vue
import { getCurrentInstance, ref, watch } from 'vue'

// store
import { navigationStore, objectStore } from '../../store/store.js'

// components
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import KlantenList from './KlantenList.vue'
import KlantDetails from './KlantDetails.vue'

// icons
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
	setup() {
		// get a proxy instance, this simulates the `this` keyword in a vue component, which is not available in the setup function
		const { proxy } = getCurrentInstance()

		// create a ref, which is a reactive reference to the id
		// this is needed since the component wont be re-rendered when the id changes otherwise
		const id = ref(proxy.$route.params.id || null)

		// watch the id, and update the ref when the id changes
		watch(() => proxy.$route.params.id, (newId) => {
			id.value = newId
		})

		// return the id and the navigationStore and zaakStore
		// the store is still required throughout the component, and not exporting them would break it
		return {
			navigationStore,
			objectStore,
			id,
		}
	},
}
</script>
