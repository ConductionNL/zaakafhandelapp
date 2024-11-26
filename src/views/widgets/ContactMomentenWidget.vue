<script setup>
import { navigationStore, contactMomentStore } from '../../store/store.js'
</script>

<template>
	<div class="contactmomentenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
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
import Plus from 'vue-material-design-icons/Plus.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
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
			contactMomentStore.refreshContactMomentenList()
				.then(() => {
					this.contactMomentItems = contactMomentStore.contactMomentenList.map(contactMoment => ({
						id: contactMoment.id,
						mainText: contactMoment.titel,
						subText: new Date(contactMoment.startDate).toLocaleString(),
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

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/chat-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/chat-outline.svg`
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
	},

}
</script>
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
