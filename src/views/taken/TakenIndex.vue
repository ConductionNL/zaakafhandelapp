<template>
	<NcAppContent>
		<template #list>
			<TakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen taak"
				description="Nog geen taak geselecteerd">
				<template #icon>
					<CalendarMonthOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('editTaak')">
						Taak toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<TaakDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
// vue
import { getCurrentInstance, ref, watch } from 'vue'

// store
import { navigationStore, taakStore } from '../../store/store.js'

// components
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import TakenList from './TakenList.vue'
import TaakDetails from './TaakDetails.vue'

// icons
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'

export default {
	name: 'TakenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		TakenList,
		TaakDetails,
		CalendarMonthOutline,
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
			taakStore,
			id,
		}
	},
}
</script>
