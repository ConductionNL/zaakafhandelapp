<script setup>
import { navigationStore, contactMomentStore, klantStore } from '../../store/store.js'
</script>

<template>
	<div class="contactmomentenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="contactMomentItems"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow"
				@edit="onEdit"
				@sluiten="onSluiten">
				<template #empty-content>
					<NcEmptyContent name="Geen contactmomenten gevonden">
						<template #icon>
							<ChatOutline />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<NcButton type="primary" @click="openModal">
			<template #icon>
				<Plus :size="20" />
			</template>
			Contactmoment starten
		</NcButton>

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
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'

// Entities
import { ContactMoment } from '../../entities/index.js'

// Icons
import { iconPencil, iconProgressClose } from '../../services/icons/index.js'
import Plus from 'vue-material-design-icons/Plus.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'

// Modals
import ContactMomentenForm from '../../modals/contactMomenten/ContactMomentenForm.vue'

export default {
	name: 'ContactMomentenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
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
			// widget options
			itemMenu: {
				show: {
					text: 'Bekijk',
					icon: 'icon-toggle',
				},
				edit: {
					text: 'Bewerk',
					icon: iconPencil,
				},
				sluiten: {
					text: 'Sluit',
					icon: iconProgressClose,
				},
			},
		}
	},
	mounted() {
		this.fetchUser()
	},
	methods: {
		async fetchUser() {
			this.loading = true

			const getUser = await fetch('/index.php/apps/zaakafhandelapp/me')
			const user = await getUser.json()

			const medewerkers = await fetch('/ocs/v1.php/cloud/users/details', {
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'OCS-APIRequest': 'true',
				},
			})
				.then(response => response.json())
				.then((data) => Object.values(data.ocs.data.users))

			const medewerker = medewerkers.find((medewerker) => medewerker.id === user.user.id)

			this.userEmail = medewerker.email
			this.fetchContactMomentItems()
		},
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
						avatarUrl: (() => {
							switch (contactMoment.kanaal) {
							case 'telefoon':
								return this.getItemPhoneIcon()
							case 'email':
								return this.getItemEmailIcon()
							case 'brief':
								return this.getItemMailboxIcon()
							case 'balie':
								return this.getItemAgentIcon()
							default:
								return this.getItemIcon()
							}
						})(),
					}))
				})
				.finally(() => {
					this.loading = false
				})
		},
		// === ICONS ===
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/chat-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/chat-outline.svg`
		},
		getItemPhoneIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/phone-dark.svg` : `${appLocation}/zaakafhandelapp/img/phone.svg`
		},
		getItemEmailIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/email-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/email-outline.svg`
		},
		getItemMailboxIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/mailbox-open-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/mailbox-open-outline.svg`
		},
		getItemAgentIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/face-agent-dark.svg` : `${appLocation}/zaakafhandelapp/img/face-agent.svg`
		},
		// === MODAL CONTROL ===
		/**
		 * Opens the contactmoment form modal in create/add mode
		 */
		openModal() {
			this.isContactMomentFormOpen = true
			this.contactMomentId = null
			contactMomentStore.setContactMomentItem(null)
		},
		/**
		 * runs when the contact form modal closes
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
		 */
		onShow(event) {
			this.contactMomentId = event.id
			this.isContactMomentFormOpen = true
			this.isView = true
		},
		/**
		 * runs when the user clicks on the edit button, and opens the contactmoment form modal in edit mode
		 * @param {{id: number}} event - the contactmoment item received from the widget
		 */
		onEdit(event) {
			this.contactMomentId = event.id
			this.isContactMomentFormOpen = true
			this.isView = false
		},
		/**
		 * runs when the user clicks on the "sluiten" button, and changes the status of the contactmoment to 'gesloten'
		 * @param {{id: number}} event - the contactmoment item received from the widget
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

.itemContainer {
	overflow: auto;
	margin-block-end: var(--zaa-margin-10);
}
</style>
