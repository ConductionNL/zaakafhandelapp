<script setup>
import { taakStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="takenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow"
				@statusClose="onCloseStatus"
				@statusHandled="onHandledStatus">
				<template #empty-content>
					<NcEmptyContent name="Geen open taken">
						<template #icon>
							<Folder />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<div class="buttonContainer">
			<NcButton type="primary" @click="openModal">
				<template #icon>
					<Plus :size="20" />
				</template>
				Taak aanmaken
			</NcButton>
			<NcButton type="primary" @click="fetchTaakItems">
				<template #icon>
					<Refresh :size="20" />
				</template>
				Refresh
			</NcButton>
		</div>

		<EditTaakForm v-if="isModalOpen"
			:dashboard-widget="true"
			:taak-id="taakId"
			@save-success="fetchTaakItems"
			@close-modal="closeModal" />
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'

// Entities
import { Taak } from '../../entities/index.js'

// Icons
import { iconProgressClose, iconCalendarCheckOutline } from '../../services/icons/index.js'

import Plus from 'vue-material-design-icons/Plus.vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import EditTaak from '../../modals/taken/EditTaak.vue'

export default {
	name: 'TakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
		EditTaakForm: EditTaak,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			taakItems: [],
			itemMenu: {
				show: {
					text: 'Bekijk',
					icon: 'icon-toggle',
				},
				statusClose: {
					text: 'Sluiten',
					icon: iconProgressClose,
				},
				statusHandled: {
					text: 'Taak Afhandelen',
					icon: iconCalendarCheckOutline,
				},
			},
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
			taakStore.refreshTakenList(null, true)
				.then(() => {

					this.taakItems = taakStore.takenList.map(taak => ({
						id: taak.id,
						mainText: taak.title,
						subText: `${taak.deadline ? new Date(taak.deadline).toLocaleDateString() : ''} ${taak.deadline && taak.type ? '-' : ''}  ${taak.type}`,
						avatarUrl: this.getItemIcon(),
					}))

					this.loading = false
				})
		},
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/calendar-month-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/calendar-month-outline.svg`
		},
		openModal() {
			this.isModalOpen = true
			this.taakId = null
			taakStore.setTaakItem(null)
			navigationStore.setModal('editTaak')
		},
		closeModal() {
			this.isModalOpen = false
			navigationStore.setModal(null)
		},

		onShow(event) {
			this.taakId = event.id
			this.isModalOpen = true
		},

		async onCloseStatus(event) {
			// change status to 'gesloten'
			const { data } = await taakStore.getTaak(event.id)

			if (data?.status === 'gesloten') {
				console.info('Taak is already closed')
				return
			}

			const newTaak = new Taak({
				...data,
				status: 'gesloten',
			})

			taakStore.saveTaak(newTaak)
				.then(({ response }) => {
					if (response.ok) {
						this.fetchTaakItems(null, true)
					}
				})
		},
		async onHandledStatus(event) {
			// change status to 'afgerond'
			const { data } = await taakStore.getTaak(event.id)

			if (data?.status === 'afgerond') {
				console.info('Taak is already handled')
				return
			}

			const newTaak = new Taak({
				...data,
				status: 'afgerond',
			})

			taakStore.saveTaak(newTaak)
				.then(({ response }) => {
					if (response.ok) {
						this.fetchTaakItems(null, true)
					}
				})
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

 .buttonContainer{
	display: flex;
	gap: 10px;
 }
</style>
