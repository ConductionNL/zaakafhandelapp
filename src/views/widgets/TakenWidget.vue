<script setup>
import { taakStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="takenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent name="Geen open taken">
						<template #icon>
							<Folder />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<NcButton type="primary" @click="openModal">
			<template #icon>
				<Plus :size="20" />
			</template>
			Taak aanmaken
		</NcButton>

		<TakenForm v-if="isModalOpen"
			:dashboard-widget="true"
			@save-success="fetchTaakItems" />
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import TakenForm from '../../modals/taken/EditTaak.vue'

export default {
	name: 'TakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			taakItems: [],
		}
	},

	computed: {
		items() {
			return this.taakItems
		},
	},

	mounted() {
		this.fetchTaakItems()
	},

	methods: {
		fetchTaakItems() {
			this.loading = true
			taakStore.refreshTakenList()
				.then(() => {
					this.taakItems = taakStore.takenList.map(taak => ({
						id: taak.id,
						mainText: taak.title,
						subText: taak.type,
						avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',
					}))

					this.loading = false
				})
		},
		openModal() {
			this.isModalOpen = true
			taakStore.setTaakItem(null)
			navigationStore.setModal('editTaak')
		},
		closeModal() {
			this.isModalOpen = false
			navigationStore.setModal(null)
		},
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
	},

}
</script>
<style scoped>
.takenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
	overflow: auto;
	margin-block-end: var(--zaa-margin-10);
 }
</style>
