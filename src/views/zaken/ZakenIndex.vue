<script setup>
import { translate as t } from '@nextcloud/l10n'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				:name="t('zaakafhandelapp', 'No case')"
				:description="t('zaakafhandelapp', 'No case selected yet')">
				<template #icon>
					<BriefcaseAccountOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="zaakStore.setZaakItem(null); navigationStore.setModal('zaakForm')">
						{{ t('zaakafhandelapp', 'Start case') }}
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakDetails v-if="id" :id="id" />
		</template>
	</NcAppContent>
</template>

<script>
// vue
import { getCurrentInstance, ref, watch } from 'vue'

// store
import { navigationStore, zaakStore } from '../../store/store.js'

// components
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ZakenList from './ZakenList.vue'
import ZaakDetails from './ZaakDetails.vue'

// icons
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

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
			zaakStore,
			id,
		}
	},
}
</script>
