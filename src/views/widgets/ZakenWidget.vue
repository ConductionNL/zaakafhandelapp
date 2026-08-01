<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="zakenContainer">
		<CnDataTable :rows="items"
			:columns="columns"
			:loading="loading"
			hide-header
			borderless
			row-icon="BriefcaseAccountOutline"
			:empty-text="t('zaakafhandelapp', 'No open cases')">
			<template #empty>
				<NcEmptyContent :name="t('zaakafhandelapp', 'No open cases')">
					<template #icon>
						<Folder />
					</template>
				</NcEmptyContent>
			</template>
			<template #footer>
				<NcButton variant="primary" @click="openModal">
					<template #icon>
						<Plus :size="20" />
					</template>
					{{ t('zaakafhandelapp', 'Create case') }}
				</NcButton>
			</template>
		</CnDataTable>

		<ZaakForm v-if="isModalOpen"
			:dashboard-widget="true"
			@save-success="fetchZaakItems"
			@close="closeModal" />
	</div>
</template>

<script>
// Components
import { CnDataTable } from '@conduction/nextcloud-vue'
import { NcEmptyContent, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Folder from 'vue-material-design-icons/Folder.vue'

import ZaakForm from '../../modals/zaken/WidgetZaakForm.vue'
import { WIDGET_COLUMNS } from './widgetTable.js'

export default {
	name: 'ZakenWidget',
	components: {
		CnDataTable,
		NcEmptyContent,
		NcButton,
		Plus,
		Folder,
	},
	data() {
		return {
			loading: false,
			isModalOpen: false,
			zaakItems: [],
			columns: WIDGET_COLUMNS,
		}
	},
	computed: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		items() {
			return this.zaakItems
		},
	},
	mounted() {
		this.fetchZaakItems()
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchZaakItems() {
			this.loading = true
			zaakStore.refreshZakenList()
				.then(() => {
					this.zaakItems = zaakStore.zakenList.map(zaak => ({
						id: zaak.id,
						mainText: zaak.identificatie,
						subText: zaak.zaaktype,
					}))

					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		openModal() {
			this.isModalOpen = true
			zaakStore.setZaakItem(null)
			navigationStore.setModal('zaakForm')
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		closeModal() {
			this.isModalOpen = false
			navigationStore.setModal(null)
		},
	},
}
</script>

<style scoped>
.zakenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.zakenContainer > .cn-table-container {
	overflow: auto;
}
</style>
<style>
:root {
	--zaa-margin-10: 10px;
	--zaa-margin-20: 20px;
	--zaa-margin-50: 50px;
  }
</style>
