<template>
	<NcAppContent>
		<template #list>
			<ContactMomentenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen contactmoment"
				description="Nog geen contactmoment geselecteerd">
				<template #icon>
					<CardAccountPhoneOutline />
				</template>
				<template #action>
					<NcButton type="primary"
						@click="objectStore.clearActiveObject('contactmoment'); navigationStore.setModal('contactMomentenForm')">
						Contactmoment aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<ContactMomentDetails v-if="id" :id="id" />
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
import ContactMomentenList from './ContactMomentenList.vue'
import ContactMomentDetails from './ContactMomentDetails.vue'

// icons
import CardAccountPhoneOutline from 'vue-material-design-icons/CardAccountPhoneOutline.vue'

export default {
	name: 'ContactMomentenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ContactMomentenList,
		ContactMomentDetails,
		CardAccountPhoneOutline,
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
