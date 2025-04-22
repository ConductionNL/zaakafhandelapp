<template>
	<NcAppContent>
		<template #list>
			<BesluitenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen besluit"
				description="Nog geen besluit geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="objectStore.clearActiveObject('besluit'); navigationStore.setModal('besluitForm')">
						Besluit aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<BesluitDetails v-if="id" :besluit-id="id" />
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
import BesluitenList from './BesluitenList.vue'
import BesluitDetails from './BesluitDetails.vue'

// icons
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
