<script setup>
import { navigationStore, contactMomentStore, klantStore } from '../../store/store.js'
</script>

<template>
	<div class="contactmomentenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow"
				@sluiten="onSluiten">
				<template #empty-content>
					<NcEmptyContent name="Geen contact momenten gevonden">
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
			Contact moment starten
		</NcButton>

		<ContactMomentenForm v-if="isModalOpen"
			:dashboard-widget="true"
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
			isModalOpen: false,
			searchKlantModalOpen: false,
			contactMomentItems: [],
			itemMenu: {
				// show: {
				// 	text: 'Bekijk',
				// 	icon: 'icon-toggle',
				// },
				sluiten: {
					text: 'Sluit',
					icon: this.getSluitenIcon(),
				},
			},
		}
	},

	computed: {
		items() {
			return this.contactMomentItems
		},
	},

	mounted() {
		this.fetchContactMomentItems()
	},

	methods: {
		fetchContactMomentItems() {
			this.loading = true

			Promise.all([
				contactMomentStore.refreshContactMomentenList(null, true),
				klantStore.refreshKlantenList(),
			])
				.then(([contactMomentResponse, klantResponse]) => {
					this.contactMomentItems = contactMomentResponse.entities.map(contactMoment => ({
						id: contactMoment.id,
						mainText: (() => { // this is a self calling function to get the klant name
							const klant = klantResponse.entities.find(klant => klant.id === contactMoment.klant)
							if (klant) {
								const tussenvoegsel = klant.tussenvoegsel ? klant.tussenvoegsel + ' ' : ''
								return `${klant.voornaam} ${tussenvoegsel}${klant.achternaam}`
							}
							return ''
						})(),
						subText: new Date(contactMoment.startDate).toLocaleString(),
						avatarUrl: this.getItemIcon(),
					}))
				})
				.finally(() => {
					this.loading = false
				})
		},
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/chat-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/chat-outline.svg`
		},
		getSluitenIcon() {
			const theme = getTheme()

			return theme === 'light' ? 'icon-progress-close-dark' : 'icon-progress-close-light'
		},
		openModal() {
			this.isModalOpen = true
			contactMomentStore.setContactMomentItem(null)
			navigationStore.setModal('contactMomentenForm')
		},
		closeModal() {
			this.isModalOpen = false
			navigationStore.setModal(null)
		},

		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
		async onSluiten(event) {
			// change status to 'gesloten'
			const { data } = await contactMomentStore.getContactMoment(event.id)

			if (data?.status === 'gesloten') {
				console.info('Contact moment is already closed')
				return
			}

			const newContactMoment = new ContactMoment({
				...data,
				status: 'gesloten',
			})

			contactMomentStore.saveContactMoment(newContactMoment)
				.then(({ response }) => {
					if (response.ok) {
						this.fetchContactMomentItems(null, true)
					}
				})
		},
	},

}
</script>
<style>
.icon-progress-close-dark {
	background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+DQogICAgPHBhdGgNCiAgICAgICAgZD0iTTEzIDIuMDNWNC4wNUMxNy4zOSA0LjU5IDIwLjUgOC41OCAxOS45NiAxMi45N0MxOS41IDE2LjYxIDE2LjY0IDE5LjUgMTMgMTkuOTNWMjEuOTNDMTguNSAyMS4zOCAyMi41IDE2LjUgMjEuOTUgMTFDMjEuNSA2LjI1IDE3LjczIDIuNSAxMyAyLjAzTTExIDIuMDZDOS4wNSAyLjI1IDcuMTkgMyA1LjY3IDQuMjZMNy4xIDUuNzRDOC4yMiA0Ljg0IDkuNTcgNC4yNiAxMSA0LjA2VjIuMDZNNC4yNiA1LjY3QzMgNy4xOSAyLjI1IDkuMDQgMi4wNSAxMUg0LjA1QzQuMjQgOS41OCA0LjggOC4yMyA1LjY5IDcuMUw0LjI2IDUuNjdNMi4wNiAxM0MyLjI2IDE0Ljk2IDMuMDMgMTYuODEgNC4yNyAxOC4zM0w1LjY5IDE2LjlDNC44MSAxNS43NyA0LjI0IDE0LjQyIDQuMDYgMTNIMi4wNk03LjEgMTguMzdMNS42NyAxOS43NEM3LjE4IDIxIDkuMDQgMjEuNzkgMTEgMjJWMjBDOS41OCAxOS44MiA4LjIzIDE5LjI1IDcuMSAxOC4zN00xNC41OSA4TDEyIDEwLjU5TDkuNDEgOEw4IDkuNDFMMTAuNTkgMTJMOCAxNC41OUw5LjQxIDE2TDEyIDEzLjQxTDE0LjU5IDE2TDE2IDE0LjU5TDEzLjQxIDEyTDE2IDkuNDFMMTQuNTkgOFoiDQogICAgICAgIHN0eWxlPSJmaWxsLW9wYWNpdHk6LjU7ZmlsbC1ydWxlOm5vbnplcm8iIGZpbGw9IiMwMDAwMDAiIC8+DQo8L3N2Zz4=);
}

.icon-progress-close-light {
	background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+DQogICAgPHBhdGgNCiAgICAgICAgZD0iTTEzIDIuMDNWNC4wNUMxNy4zOSA0LjU5IDIwLjUgOC41OCAxOS45NiAxMi45N0MxOS41IDE2LjYxIDE2LjY0IDE5LjUgMTMgMTkuOTNWMjEuOTNDMTguNSAyMS4zOCAyMi41IDE2LjUgMjEuOTUgMTFDMjEuNSA2LjI1IDE3LjczIDIuNSAxMyAyLjAzTTExIDIuMDZDOS4wNSAyLjI1IDcuMTkgMyA1LjY3IDQuMjZMNy4xIDUuNzRDOC4yMiA0Ljg0IDkuNTcgNC4yNiAxMSA0LjA2VjIuMDZNNC4yNiA1LjY3QzMgNy4xOSAyLjI1IDkuMDQgMi4wNSAxMUg0LjA1QzQuMjQgOS41OCA0LjggOC4yMyA1LjY5IDcuMUw0LjI2IDUuNjdNMi4wNiAxM0MyLjI2IDE0Ljk2IDMuMDMgMTYuODEgNC4yNyAxOC4zM0w1LjY5IDE2LjlDNC44MSAxNS43NyA0LjI0IDE0LjQyIDQuMDYgMTNIMi4wNk03LjEgMTguMzdMNS42NyAxOS43NEM3LjE4IDIxIDkuMDQgMjEuNzkgMTEgMjJWMjBDOS41OCAxOS44MiA4LjIzIDE5LjI1IDcuMSAxOC4zN00xNC41OSA4TDEyIDEwLjU5TDkuNDEgOEw4IDkuNDFMMTAuNTkgMTJMOCAxNC41OUw5LjQxIDE2TDEyIDEzLjQxTDE0LjU5IDE2TDE2IDE0LjU5TDEzLjQxIDEyTDE2IDkuNDFMMTQuNTkgOFoiDQogICAgICAgIHN0eWxlPSJmaWxsLXJ1bGU6bm9uemVybyIgZmlsbD0iI2ZmZmZmZiIgLz4NCjwvc3ZnPg==);
}

</style>
<style scoped>
.contactmomentenContainer{
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
