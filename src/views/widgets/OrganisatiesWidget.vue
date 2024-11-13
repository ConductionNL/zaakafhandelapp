<script setup>
import { klantStore } from '../../store/store.js'
</script>

<template>
	<div class="openZakenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
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
		</div>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton, NcTextField } from '@nextcloud/vue'
import Search from 'vue-material-design-icons/Magnify.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'

export default {
	name: 'OrganisatiesWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Search,
		NcTextField,
	},

	data() {
		return {
			loading: false,
			organisatieItems: [],
			searchOrganisatie: '',
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
						avatarUrl: '/apps-extra/zaakafhandelapp/img/office-building-outline.svg',
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
						avatarUrl: '/apps-extra/zaakafhandelapp/img/office-building-outline.svg',
					}))
					this.loading = false
				})
				.finally(() => {
					this.loading = false
				})
		},
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
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
