<template>
	<NcAppContent>
		<template #list>
			<ZaakTypeList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen Zaaktype"
				description="Nog geen zaaktype geselecteerd">
				<template #icon>
					<AlphaTBoxOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="zaakTypeStore.setZaakTypeItem(null); navigationStore.setModal('zaaktypeForm')">
						Zaaktype toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakTypeDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
// vue
import { getCurrentInstance, ref, watch } from 'vue'

// store
import { navigationStore, zaakTypeStore } from '../../store/store.js'

// components
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ZaakTypeList from './ZaakTypenList.vue'
import ZaakTypeDetails from './ZaakTypeDetails.vue'

// icons
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline.vue'

export default {
	name: 'ZakenTypenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ZaakTypeList,
		ZaakTypeDetails,
		// icons
		AlphaTBoxOutline,
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
			zaakTypeStore,
			id,
		}
	},
}
</script>
