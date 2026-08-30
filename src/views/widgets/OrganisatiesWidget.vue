<script setup>
import { translate as t } from '@nextcloud/l10n'
import { klantStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="openZakenContainer">
		<CnDataTable
			:rows="items"
			:columns="columns"
			:loading="loading"
			hideHeader
			borderless
			rowIcon="OfficeBuildingOutline"
			:emptyText="t('zaakafhandelapp', 'No organisations found')"
			@rowClick="onShow">
			<template #empty>
				<NcEmptyContent
					:name="t('zaakafhandelapp', 'No organisations found')">
					<template #icon>
						<OfficeBuildingOutline />
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
				</NcActions>
			</template>
			<template #footer>
				<div class="searchContainer">
					<NcTextField
						v-model="searchOrganisatie"
						:disabled="loading"
						:label="t('zaakafhandelapp', 'Search by company name')"
						maxlength="255"
						class="OrgSearchField" />

					<NcButton
						variant="primary"
						:disabled="loading"
						class="searchButton"
						@click="search">
						<template #icon>
							<Search :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Search') }}
					</NcButton>
				</div>
			</template>
		</CnDataTable>

		<ViewKlant
			v-if="isModalOpen"
			:dashboardWidget="true"
			:klantId="klantStore.widgetKlantId"
			@saveSuccess="fetchOrganisatieItems"
			@closeModal="() => (isModalOpen = false)" />
	</div>
</template>

<script>
// Components
import { CnDataTable } from '@conduction/nextcloud-vue'
import {
	NcActionButton,
	NcActions,
	NcButton,
	NcEmptyContent,
	NcTextField,
} from '@nextcloud/vue'
import Search from 'vue-material-design-icons/Magnify.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import ViewKlant from '../../modals/klanten/ViewKlant.vue'
import { WIDGET_COLUMNS } from './widgetTable.js'

export default {
	name: 'OrganisatiesWidget',

	components: {
		CnDataTable,
		NcEmptyContent,
		NcButton,
		NcActions,
		NcActionButton,
		Search,
		NcTextField,
		OfficeBuildingOutline,
		ViewKlant,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			organisatieItems: [],
			searchOrganisatie: '',
			selectedKlantId: '',
			columns: WIDGET_COLUMNS,
		}
	},

	computed: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		items() {
			return this.organisatieItems
		},
	},

	mounted() {
		this.fetchOrganisatieItems()
	},

	methods: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchOrganisatieItems() {
			this.loading = true
			klantStore.searchOrganisations().then(() => {
				this.organisatieItems = klantStore.klantenList.map(
					(organisatie) => ({
						id: organisatie.id,
						mainText: organisatie.bedrijfsnaam,
						subText: organisatie.websiteUrl,
					}),
				)

				this.loading = false
			})
		},

		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-002
		 */
		search() {
			this.loading = true
			klantStore
				.searchOrganisations(this.searchOrganisatie)
				.then(() => {
					this.organisatieItems = klantStore.klantenList.map(
						(organisatie) => ({
							id: organisatie.id,
							mainText: organisatie.bedrijfsnaam,
							subText: organisatie.websiteUrl,
						}),
					)
					this.loading = false
				})
				.finally(() => {
					this.loading = false
				})
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
	},
}
</script>

<style scoped>
.openZakenContainer {
	display: flex;
	justify-content: space-between;
	flex-direction: column;
	height: 100%;
}

.openZakenContainer > .cn-table-container {
	overflow: auto;
}

.searchContainer {
	display: flex;
	align-items: end;
	gap: 10px;
	flex: 1;
}

.OrgSearchField {
	width: auto;
}

.searchButton {
	min-width: min-content !important;
}
</style>
