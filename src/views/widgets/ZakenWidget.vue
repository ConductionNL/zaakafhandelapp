<script setup>
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="zakenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent name="Geen open zaken">
						<template #icon>
							<Folder />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<NcButton type="primary" @click="openModal">
			<template #icon>
				<Plus :size="20" />
			</template>
			Zaak aanmaken
		</NcButton>

		<ZaakForm v-if="isModalOpen"
			:dashboard-widget="true"
			@save-success="fetchZaakItems"
			@close="closeModal" />
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import Plus from 'vue-material-design-icons/Plus.vue'
import Folder from 'vue-material-design-icons/Folder.vue'

import ZaakForm from '../../modals/zaken/WidgetZaakForm.vue'

export default {
	name: 'ZakenWidget',
	components: {
		NcDashboardWidget,
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
		}
	},
	computed: {
		items() {
			return this.zaakItems
		},
	},
	mounted() {
		this.fetchZaakItems()
	},
	methods: {
		fetchZaakItems() {
			this.loading = true
			zaakStore.refreshZakenList()
				.then(() => {
					this.zaakItems = zaakStore.zakenList.map(zaak => ({
						id: zaak.id,
						mainText: zaak.identificatie,
						subText: zaak.zaaktype,
						avatarUrl: this.getItemIcon(),
					}))

					this.loading = false
				})
		},
		openModal() {
			this.isModalOpen = true
			zaakStore.setZaakItem(null)
			navigationStore.setModal('zaakForm')
		},
		closeModal() {
			this.isModalOpen = false
			navigationStore.setModal(null)
		},
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/briefcase-account-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/briefcase-account-outline.svg`
		},
		search() {
			console.info('click')
		},
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
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
.itemContainer{
	overflow: auto;
	margin-block-end: var(--zaa-margin-10);
 }
</style>
<style>
:root {
	--zaa-margin-10: 10px;
	--zaa-margin-20: 20px;
	--zaa-margin-50: 50px;
  }
</style>
