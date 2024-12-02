<script setup>
import { klantStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="openZakenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent name="Geen organisaties gevonden">
						<template #icon>
							<OfficeBuildingOutline />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<div class="searchContainer">
			<NcTextField :disabled="loading"
				label="Zoeken op bedrijfsnaam"
				maxlength="255"
				class="searchField"
				:value.sync="searchOrganisatie" />

			<NcButton type="primary"
				:disabled="loading"
				class="searchButton"
				@click="search">
				<template #icon>
					<Search :size="20" />
				</template>
				Zoeken
			</NcButton>

			<ViewKlantRegister v-if="isModalOpen"
				:dashboard-widget="true"
				:klant-id="klantStore.widgetKlantId"
				@save-success="fetchOrganisatieItems" />
		</div>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton, NcTextField } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import Search from 'vue-material-design-icons/Magnify.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import ViewKlantRegister from '../../modals/klantRegister/ViewKlantRegister.vue'

export default {
	name: 'OrganisatiesWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Search,
		NcTextField,
		OfficeBuildingOutline,
		ViewKlantRegister,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			organisatieItems: [],
			searchOrganisatie: '',
			selectedKlantId: '',
			itemMenu: {
				show: {
					text: 'Bekijk',
					icon: 'icon-toggle',
				},
			},
		}
	},

	computed: {
		items() {
			return this.organisatieItems
		},
	},

	mounted() {
		this.fetchOrganisatieItems()
	},

	methods: {
		fetchOrganisatieItems() {
			this.loading = true
			klantStore.searchOrganisations()
				.then(() => {
					this.organisatieItems = klantStore.klantenList.map(organisatie => ({
						id: organisatie.id,
						mainText: organisatie.bedrijfsnaam,
						subText: organisatie.websiteUrl,
						avatarUrl: this.getItemIcon(),
					}))

					this.loading = false
				})
		},
		search() {
			this.loading = true
			klantStore.searchOrganisations(this.searchOrganisatie)
				.then(() => {
					this.organisatieItems = klantStore.klantenList.map(organisatie => ({
						id: organisatie.id,
						mainText: organisatie.bedrijfsnaam,
						subText: organisatie.websiteUrl,
						avatarUrl: this.getItemIcon(),
					}))
					this.loading = false
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

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/office-building-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/office-building-outline.svg`
		},
		onShow(item) {
			klantStore.setWidgetKlantId(item.id)
			this.isModalOpen = true
			navigationStore.setModal('viewKlantRegister')

		},

	},

}
</script>
<style scoped>
.openZakenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
   overflow: auto;
}
.searchContainer {
	display: flex;
	align-items: end;
	gap: 10px;
}
.searchField {
	width: auto;
}
.searchButton {
	min-width: min-content !important;
}
</style>
