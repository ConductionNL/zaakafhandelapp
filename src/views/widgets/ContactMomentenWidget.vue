<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, contactMomentStore, klantStore } from '../../store/store.js'
</script>

<template>
	<div class="contactmomentenContainer">
		<CnDataTable :rows="contactMomentItems"
			:columns="columns"
			:loading="loading"
			hide-header
			borderless
			:row-icon="contactMomentIcon"
			:empty-text="t('zaakafhandelapp', 'No contact moments found')"
			@row-click="onShow">
			<template #empty>
				<NcEmptyContent :name="t('zaakafhandelapp', 'No contact moments found')">
					<template #icon>
						<ChatOutline />
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
					<NcActionButton :icon="iconPencil"
						close-after-click
						@click="onEdit(row)">
						{{ t('zaakafhandelapp', 'Edit') }}
					</NcActionButton>
					<NcActionButton :icon="iconProgressClose"
						close-after-click
						@click="onSluiten(row)">
						{{ t('zaakafhandelapp', 'Close') }}
					</NcActionButton>
				</NcActions>
			</template>
			<template #footer>
				<NcButton type="primary" @click="openModal">
					<template #icon>
						<Plus :size="20" />
					</template>
					{{ t('zaakafhandelapp', 'Start contact moment') }}
				</NcButton>
			</template>
		</CnDataTable>

		<ContactMomentenForm v-if="isContactMomentFormOpen"
			:dashboard-widget="true"
			:contact-moment-id="contactMomentId"
			:is-view="isView"
			@save-success="fetchContactMomentItems"
			@close-modal="closeModal" />
	</div>
</template>

<script>
// Components
import { CnDataTable } from '@conduction/nextcloud-vue'
import { NcEmptyContent, NcButton, NcActions, NcActionButton } from '@nextcloud/vue'

// Entities
import { ContactMoment } from '../../entities/index.js'

// Icons
import { iconPencil, iconProgressClose } from '../../services/icons/index.js'
import Plus from 'vue-material-design-icons/Plus.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'

// Modals
import ContactMomentenForm from '../../modals/contactMomenten/ContactMomentenForm.vue'
import { WIDGET_COLUMNS, contactMomentIcon } from './widgetTable.js'

export default {
	name: 'ContactMomentenWidget',

	components: {
		CnDataTable,
		NcEmptyContent,
		NcButton,
		NcActions,
		NcActionButton,
		Plus,
		ChatOutline,
		ContactMomentenForm,
	},

	data() {
		return {
			loading: false,
			/**
			 * determines if the contactmoment form modal is open
			 */
			isContactMomentFormOpen: false,
			userEmail: null,
			contactMomentItems: [],
			// contactmoment form props
			contactMomentId: null,
			isView: false,
			columns: WIDGET_COLUMNS,
			// Per-kanaal leading row icon resolver (phone/email/brief/balie),
			// handed to CnDataTable's `rowIcon` as a (row) => name function.
			contactMomentIcon,
			iconPencil,
			iconProgressClose,
		}
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
			const { ocs: { data: user } } = await getUser.json()

			const medewerkers = await fetch('/ocs/v1.php/cloud/users/details', {
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'OCS-APIRequest': 'true',
				},
			})
				.then(response => response.json())
				.then((data) => Object.values(data.ocs.data.users))

			const medewerker = medewerkers.find((medewerker) => medewerker.id === user.id)

			this.userEmail = medewerker.email
			this.fetchContactMomentItems()
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchContactMomentItems() {
			this.loading = true

			Promise.all([
				contactMomentStore.refreshContactMomentenList(null, true, this.userEmail),
				klantStore.refreshKlantenList(),
			])
				.then(([contactMomentResponse, klantResponse]) => {
					this.contactMomentItems = contactMomentResponse.entities.map(contactMoment => ({
						id: contactMoment.id,
						mainText: (() => { // this is a self calling function to get the klant name, which is why you don't see it being called anywhere
							const klant = klantResponse.entities.find(klant => klant.id === contactMoment.klant)
							if (klant) {
								return klant.type === 'persoon' ? `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}` : `${klant.bedrijfsnaam}`
							}
							return ''
						})(),
						subText: new Date(contactMoment.startDate).toLocaleString(),
						// drives the per-kanaal leading row icon (contactMomentIcon)
						kanaal: contactMoment.kanaal,
					}))
				})
				.finally(() => {
					this.loading = false
				})
		},
		// === MODAL CONTROL ===
		/**
		 * Opens the contactmoment form modal in create/add mode
		  *
		  * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		openModal() {
			this.isContactMomentFormOpen = true
			this.contactMomentId = null
			contactMomentStore.setContactMomentItem(null)
		},
		/**
		 * runs when the contact form modal closes
		  *
		  * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-005
		 */
		closeModal() {
			this.isContactMomentFormOpen = false
			this.isView = false
			navigationStore.setModal(null)
			this.fetchContactMomentItems()
		},
		// === EVENTS ===
		/**
		 * runs when the user clicks on the show button, and opens the contactmoment form modal in view mode
		 * @param {{id: number}} event - the contactmoment item received from the widget
		  *
		  * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */
		onShow(event) {
			this.contactMomentId = event.id
			this.isContactMomentFormOpen = true
			this.isView = true
		},
		/**
		 * runs when the user clicks on the edit button, and opens the contactmoment form modal in edit mode
		 * @param {{id: number}} event - the contactmoment item received from the widget
		  *
		  * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */
		onEdit(event) {
			this.contactMomentId = event.id
			this.isContactMomentFormOpen = true
			this.isView = false
		},
		/**
		 * runs when the user clicks on the "sluiten" button, and changes the status of the contactmoment to 'gesloten'
		 * @param {{id: number}} event - the contactmoment item received from the widget
		  *
		  * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */
		async onSluiten(event) {
			const { data } = await contactMomentStore.getContactMoment(event.id)

			if (data?.status === 'gesloten') {
				console.info('Contactmoment is already closed')
				return
			}

			const newContactMoment = new ContactMoment({
				...data,
				status: 'gesloten',
			})

			contactMomentStore.saveContactMoment(newContactMoment, { redirect: false })
				.then(({ response }) => {
					if (response.ok) {
						this.fetchContactMomentItems(null, true)
					}
				})
		},
	},

}
</script>
<style scoped>
.contactmomentenContainer {
	display: flex;
	justify-content: space-between;
	flex-direction: column;
	height: 100%;
}

.contactmomentenContainer > .cn-table-container {
	overflow: auto;
}
</style>
