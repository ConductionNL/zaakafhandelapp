<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, taakStore } from '../../store/store.js'
</script>

<template>
	<div class="takenContainer">
		<CnDataTable
			:rows="items"
			:columns="columns"
			:loading="loading"
			hideHeader
			borderless
			rowIcon="CalendarMonthOutline"
			:emptyText="t('zaakafhandelapp', 'No open tasks')"
			@rowClick="onShow">
			<template #empty>
				<NcEmptyContent :name="t('zaakafhandelapp', 'No open tasks')">
					<template #icon>
						<Folder />
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
						:icon="iconProgressClose"
						closeAfterClick
						@click="onCloseStatus(row)">
						{{ t('zaakafhandelapp', 'Close') }}
					</NcActionButton>
					<NcActionButton
						:icon="iconCalendarCheckOutline"
						closeAfterClick
						@click="onHandledStatus(row)">
						{{ t('zaakafhandelapp', 'Complete task') }}
					</NcActionButton>
				</NcActions>
			</template>
			<template #footer>
				<div class="buttonContainer">
					<NcButton variant="primary" @click="openModal">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Create task') }}
					</NcButton>
					<NcButton variant="primary" @click="fetchTaakItems">
						<template #icon>
							<Refresh :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Refresh') }}
					</NcButton>
				</div>
			</template>
		</CnDataTable>

		<EditTaakForm
			v-if="isModalOpen"
			:dashboardWidget="true"
			:taakId="taakId"
			@saveSuccess="fetchTaakItems"
			@closeModal="closeModal" />
	</div>
</template>

<script>
// Components
import { CnDataTable } from '@conduction/nextcloud-vue'
import { NcActionButton, NcActions, NcButton, NcEmptyContent } from '@nextcloud/vue'
import Folder from 'vue-material-design-icons/Folder.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import EditTaak from '../../modals/taken/EditTaak.vue'
// Entities
import { Taak } from '../../entities/index.js'
// Icons
import {
	iconCalendarCheckOutline,
	iconProgressClose,
} from '../../services/icons/index.js'
import { WIDGET_COLUMNS } from './widgetTable.js'

export default {
	name: 'TakenWidget',

	components: {
		CnDataTable,
		NcEmptyContent,
		NcButton,
		NcActions,
		NcActionButton,
		Plus,
		Folder,
		Refresh,
		EditTaakForm: EditTaak,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			taakItems: [],
			taakId: null,
			userEmail: null,
			columns: WIDGET_COLUMNS,
			iconProgressClose,
			iconCalendarCheckOutline,
		}
	},

	computed: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		items() {
			return this.taakItems
		},
	},

	mounted() {
		this.fetchUser()
	},

	methods: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		async fetchUser() {
			this.loading = true

			const getUser = await fetch('/ocs/v2.php/cloud/user', {
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'OCS-APIRequest': 'true',
				},
			})
			// Destructure the response to directly access `result.ocs.data`
			const {
				ocs: { data: user },
			} = await getUser.json()

			const medewerkers = await fetch('/ocs/v1.php/cloud/users/details', {
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'OCS-APIRequest': 'true',
				},
			})
				.then((response) => response.json())
				.then((data) => Object.values(data.ocs.data.users))

			const medewerker = medewerkers.find(
				(medewerker) => medewerker.id === user.id,
			)

			this.userEmail = medewerker.email
			this.fetchTaakItems()
		},

		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchTaakItems() {
			this.loading = true
			taakStore.refreshTakenList(null, true, this.userEmail).then(() => {
				this.taakItems = taakStore.takenList.map((taak) => ({
					id: taak.id,
					mainText: taak.title,
					subText: `${taak.deadline ? new Date(taak.deadline).toLocaleDateString() : ''} ${taak.deadline && taak.type ? '-' : ''}  ${taak.type}`,
				}))

				this.loading = false
			})
		},

		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		openModal() {
			this.isModalOpen = true
			this.taakId = null
			taakStore.setTaakItem(null)
			navigationStore.setModal('editTaak')
		},

		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		closeModal() {
			this.isModalOpen = false
			navigationStore.setModal(null)
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */

		onShow(event) {
			this.taakId = event.id
			this.isModalOpen = true
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */

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

			taakStore.saveTaak(newTaak).then(({ response }) => {
				if (response.ok) {
					this.fetchTaakItems(null, true)
				}
			})
		},

		/**
		 * @param event
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */
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

			taakStore.saveTaak(newTaak).then(({ response }) => {
				if (response.ok) {
					this.fetchTaakItems(null, true)
				}
			})
		},
	},
}
</script>

<style scoped>
.takenContainer {
	display: flex;
	justify-content: space-between;
	flex-direction: column;
	height: 100%;
}

.takenContainer > .cn-table-container {
	overflow: auto;
}

.buttonContainer {
	display: flex;
	gap: 10px;
}
</style>
