<script setup>
import { translate as t } from '@nextcloud/l10n'
import { contactMomentStore, klantStore, navigationStore, taakStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="personenContainer">
		<CnDataTable :rows="personenItems"
			:columns="columns"
			:loading="loading"
			:loading-text="t('zaakafhandelapp', 'Loading person...')"
			hide-header
			borderless
			row-icon="AccountOutline"
			:empty-text="t('zaakafhandelapp', 'No persons found')"
			@row-click="onShow">
			<template #empty>
				<NcEmptyContent :name="t('zaakafhandelapp', 'No persons found')">
					<template #icon>
						<AccountOutline />
					</template>
				</NcEmptyContent>
			</template>
			<template #row-actions="{ row }">
				<NcActions>
					<NcActionButton icon="icon-toggle"
						close-after-click
						@click="onShow(row)">
						{{ t('zaakafhandelapp', 'View') }}
					</NcActionButton>
					<NcActionButton :icon="iconBriefcaseAccountOutline"
						close-after-click
						@click="() => (zaakFormModalOpen = true)">
						{{ t('zaakafhandelapp', 'Start case') }}
					</NcActionButton>
					<NcActionButton :icon="iconCardAccountPhoneOutline"
						close-after-click
						@click="() => (contactmomentModalOpen = true)">
						{{ t('zaakafhandelapp', 'Start contact moment') }}
					</NcActionButton>
					<NcActionButton :icon="iconCalendarMonthOutline"
						close-after-click
						@click="() => (taakModalOpen = true)">
						{{ t('zaakafhandelapp', 'Start task') }}
					</NcActionButton>
				</NcActions>
			</template>
			<template #footer>
				<NcButton type="primary"
					:disabled="loading"
					class="searchButton"
					@click="() => (searchKlantModalOpen = true)">
					<template #icon>
						<Search :size="20" />
					</template>
					{{ t('zaakafhandelapp', 'Search') }}
				</NcButton>
			</template>
		</CnDataTable>

		<SearchKlantModal v-if="searchKlantModalOpen"
			:dashboard-widget="true"
			starting-type="persoon"
			@selected-klant="createKlantItems($event)"
			@close-modal="() => (searchKlantModalOpen = false)" />

		<ViewKlant v-if="isModalOpen"
			:dashboard-widget="true"
			:klant-id="selectedKlantId"
			@close-modal="() => (isModalOpen = false)" />

		<ZaakForm v-if="zaakFormModalOpen"
			:dashboard-widget="true"
			:klant-id="selectedKlantId"
			@close-modal="() => (zaakFormModalOpen = false)"
			@save-success="fetchZaakItems" />

		<ContactMomentenForm v-if="contactmomentModalOpen"
			:dashboard-widget="true"
			:klant-id="selectedKlantId"
			@close-modal="() => (contactmomentModalOpen = false)"
			@save-success="fetchContactMomentenItems" />

		<EditTaak v-if="taakModalOpen"
			:dashboard-widget="true"
			client-type="klant"
			:klant-id="selectedKlantId"
			@close-modal="() => (taakModalOpen = false)"
			@save-success="fetchTaakItems" />
	</div>
</template>

<script>
// Components
import { CnDataTable } from '@conduction/nextcloud-vue'
import { NcEmptyContent, NcButton, NcActions, NcActionButton } from '@nextcloud/vue'

import { iconCalendarMonthOutline, iconCardAccountPhoneOutline, iconBriefcaseAccountOutline } from '../../services/icons/index.js'

// icons
import Search from 'vue-material-design-icons/Magnify.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'

// Modals
import ViewKlant from '../../modals/klanten/ViewKlant.vue'
import SearchKlantModal from '../../modals/klanten/SearchKlantModal.vue'
import ZaakForm from '../../modals/zaken/ZaakForm.vue'
import ContactMomentenForm from '../../modals/contactMomenten/ContactMomentenForm.vue'
import EditTaak from '../../modals/taken/EditTaak.vue'
import { WIDGET_COLUMNS } from './widgetTable.js'

export default {
	name: 'PersonenWidget',

	components: {
		CnDataTable,
		NcEmptyContent,
		NcButton,
		NcActions,
		NcActionButton,
		Search,
		AccountOutline,
		ViewKlant,
		SearchKlantModal,
		ZaakForm,
		ContactMomentenForm,
		EditTaak,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			personenItems: [],
			searchPerson: '',
			selectedKlantId: '',
			searchKlantModalOpen: false,
			zaakFormModalOpen: false,
			contactmomentModalOpen: false,
			taakModalOpen: false,
			columns: WIDGET_COLUMNS,
			iconCalendarMonthOutline,
			iconCardAccountPhoneOutline,
			iconBriefcaseAccountOutline,
		}
	},

	methods: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		createKlantItems(klant) {
			this.selectedKlantId = klant.id

			this.personenItems = [{
				id: klant.id,
				mainText: `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}`,
				subText: klant.emailadres,
			}]
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		openSearchKlantModal() {
			this.searchKlantModalOpen = true
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		closeSearchKlantModal() {
			this.searchKlantModalOpen = false
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */
		onShow(item) {
			klantStore.setWidgetKlantId(item.id)
			this.isModalOpen = true
			navigationStore.setModal('viewKlant')
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchZaakItems() {
			this.loading = true
			zaakStore.refreshZakenList()
				.then(() => {
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchContactMomentenItems() {
			this.loading = true
			contactMomentStore.refreshContactMomentenList()
				.then(() => {
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchTaakItems() {
			this.loading = true
			taakStore.refreshTakenList()
				.then(() => {
					this.loading = false
				})
		},
	},

}
</script>
<style scoped>
.personenContainer {
  display: flex;
  justify-content: space-between;
  flex-direction: column;
  height: 100%;
}
.personenContainer > .cn-table-container {
  overflow: auto;
}
.searchButton {
  min-width: min-content !important;
}
</style>
