<script setup>
import { translate as t } from '@nextcloud/l10n'
import {
	contactMomentStore,
	klantStore,
	navigationStore,
	taakStore,
	zaakStore,
} from '../../store/store.js'
</script>

<template>
	<div class="personenContainer">
		<CnDataTable
			:rows="personenItems"
			:columns="columns"
			:loading="loading"
			:loadingText="t('zaakafhandelapp', 'Loading person...')"
			hideHeader
			borderless
			rowIcon="AccountOutline"
			:emptyText="t('zaakafhandelapp', 'No persons found')"
			@rowClick="onShow">
			<template #empty>
				<NcEmptyContent :name="t('zaakafhandelapp', 'No persons found')">
					<template #icon>
						<AccountOutline />
					</template>
				</NcEmptyContent>
			</template>
			<template #row-actions="{ row }">
				<NcActions>
					<NcActionButton
						icon="icon-toggle"
						closeAfterClick
						@click="onShow(row)">
						{{ t('zaakafhandelapp', 'View') }}
					</NcActionButton>
					<NcActionButton
						:icon="iconBriefcaseAccountOutline"
						closeAfterClick
						@click="() => (zaakFormModalOpen = true)">
						{{ t('zaakafhandelapp', 'Start case') }}
					</NcActionButton>
					<NcActionButton
						:icon="iconCardAccountPhoneOutline"
						closeAfterClick
						@click="() => (contactmomentModalOpen = true)">
						{{ t('zaakafhandelapp', 'Start contact moment') }}
					</NcActionButton>
					<NcActionButton
						:icon="iconCalendarMonthOutline"
						closeAfterClick
						@click="() => (taakModalOpen = true)">
						{{ t('zaakafhandelapp', 'Start task') }}
					</NcActionButton>
				</NcActions>
			</template>
			<template #footer>
				<NcButton
					variant="primary"
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

		<SearchKlantModal
			v-if="searchKlantModalOpen"
			:dashboardWidget="true"
			startingType="persoon"
			@selectedKlant="createKlantItems($event)"
			@closeModal="() => (searchKlantModalOpen = false)" />

		<ViewKlant
			v-if="isModalOpen"
			:dashboardWidget="true"
			:klantId="selectedKlantId"
			@closeModal="() => (isModalOpen = false)" />

		<ZaakForm
			v-if="zaakFormModalOpen"
			:dashboardWidget="true"
			:klantId="selectedKlantId"
			@closeModal="() => (zaakFormModalOpen = false)"
			@saveSuccess="fetchZaakItems" />

		<ContactMomentenForm
			v-if="contactmomentModalOpen"
			:dashboardWidget="true"
			:klantId="selectedKlantId"
			@closeModal="() => (contactmomentModalOpen = false)"
			@saveSuccess="fetchContactMomentenItems" />

		<EditTaak
			v-if="taakModalOpen"
			:dashboardWidget="true"
			clientType="klant"
			:klantId="selectedKlantId"
			@closeModal="() => (taakModalOpen = false)"
			@saveSuccess="fetchTaakItems" />
	</div>
</template>

<script>
// Components
import { CnDataTable } from '@conduction/nextcloud-vue'
import { NcActionButton, NcActions, NcButton, NcEmptyContent } from '@nextcloud/vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
// icons
import Search from 'vue-material-design-icons/Magnify.vue'
import ContactMomentenForm from '../../modals/contactMomenten/ContactMomentenForm.vue'
import SearchKlantModal from '../../modals/klanten/SearchKlantModal.vue'
// Modals
import ViewKlant from '../../modals/klanten/ViewKlant.vue'
import EditTaak from '../../modals/taken/EditTaak.vue'
import ZaakForm from '../../modals/zaken/ZaakForm.vue'
import {
	iconBriefcaseAccountOutline,
	iconCalendarMonthOutline,
	iconCardAccountPhoneOutline,
} from '../../services/icons/index.js'
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
		 * @param klant
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		createKlantItems(klant) {
			this.selectedKlantId = klant.id

			this.personenItems = [
				{
					id: klant.id,
					mainText: `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}`,
					subText: klant.emailadres,
				},
			]
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
		 * @param item
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
			zaakStore.refreshZakenList().then(() => {
				this.loading = false
			})
		},

		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchContactMomentenItems() {
			this.loading = true
			contactMomentStore.refreshContactMomentenList().then(() => {
				this.loading = false
			})
		},

		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchTaakItems() {
			this.loading = true
			taakStore.refreshTakenList().then(() => {
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
