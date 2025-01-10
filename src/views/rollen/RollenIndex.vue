<template>
	<NcAppContent>
		<template #list>
			<RollenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen Rol"
				description="Nog geen rol geselecteerd">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="rolStore.setRolItem(null); navigationStore.setModal('rolForm')">
						Rol starten
					</NcButton>
				</template>
			</NcEmptyContent>
			<RolDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
// vue
import { getCurrentInstance, ref, watch } from 'vue'

// store
import { navigationStore, rolStore } from '../../store/store.js'

// components
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import RollenList from './RollenList.vue'
import RolDetails from './RolDetails.vue'

// icons
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'RollenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		RollenList,
		RolDetails,
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

		// return the id and the navigationStore and rolStore
		// the store is still required throughout the component, and not exporting them would break it
		return {
			navigationStore,
			rolStore,
			id,
		}
	},
}
</script>
