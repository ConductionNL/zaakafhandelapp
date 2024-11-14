<script setup>
import { berichtStore, navigationStore } from '../../store/store.js'
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

		<BerichtForm v-if="isModalOpen"
			:dashboard-widget="true"
			@save-success="fetchBerichtItems" />
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import Plus from 'vue-material-design-icons/Plus.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import BerichtForm from '../../modals/berichten/EditBericht.vue'

export default {
	name: 'ContactMomentenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			berichtItems: [],
		}
	},

	computed: {
		items() {
			return this.berichtItems
		},
	},

	mounted() {
		this.fetchBerichtItems()
	},

	methods: {
		fetchBerichtItems() {
			this.loading = true
			berichtStore.refreshBerichtenList()
				.then(() => {
					this.berichtItems = berichtStore.berichtenList.map(bericht => ({
						id: bericht.id,
						mainText: bericht.title,
						subText: bericht.aanmaakDatum,
						avatarUrl: this.getItemIcon(),
					}))

					this.loading = false
				})
		},
		getItemIcon() {
			const theme = getTheme()
			return theme === 'light' ? '/apps-extra/zaakafhandelapp/img/chat-outline-dark.svg' : '/apps-extra/zaakafhandelapp/img/chat-outline.svg'
		},
		openModal() {
			this.isModalOpen = true
			berichtStore.setBerichtItem(null)
			navigationStore.setModal('editBericht')
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
