<script setup>
import { klantStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="personenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="personenItems"
				:item-menu="itemMenu"
				@show="onShow">
				<template #empty-content>
					<div>
						<NcEmptyContent v-if="loading" name="Persoon laden...">
							<template #icon>
								<NcLoadingIcon />
							</template>
						</NcEmptyContent>
						<NcEmptyContent v-if="!loading" name="Geen personen gevonden">
							<template #icon>
								<AccountOutline />
							</template>
						</NcEmptyContent>
					</div>
				</template>
			</NcDashboardWidget>
		</div>

		<div class="searchContainer">
			<NcButton type="primary"
				:disabled="loading"
				class="searchButton"
				@click="() => (searchKlantModalOpen = true)">
				<template #icon>
					<Search :size="20" />
				</template>
				Zoek
			</NcButton>

			<ViewKlant v-if="isModalOpen"
				:dashboard-widget="true"
				:klant-id="klantStore.widgetKlantId"
				@close-modal="() => (isModalOpen = false)" />

			<SearchKlantModal v-if="searchKlantModalOpen"
				:dashboard-widget="true"
				starting-type="persoon"
				@selected-klant="createKlantItems($event)"
				@close-modal="() => (searchKlantModalOpen = false)" />
		</div>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton, NcLoadingIcon } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import Search from 'vue-material-design-icons/Magnify.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import ViewKlant from '../../modals/klanten/ViewKlant.vue'
import SearchKlantModal from '../../modals/klanten/SearchKlantModal.vue'

export default {
	name: 'PersonenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Search,
		AccountOutline,
		ViewKlant,
		SearchKlantModal,
		NcLoadingIcon,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			personenItems: [],
			searchPerson: '',
			selectedKlantId: '',
			searchKlantModalOpen: false,
			itemMenu: {
				show: {
					text: 'Bekijk',
					icon: 'icon-toggle',
				},
			},
		}
	},

	methods: {
		createKlantItems(klant) {
			this.personenItems = [{
				id: klant.id,
				mainText: `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}`,
				subText: klant.emailadres,
				avatarUrl: this.getItemIcon(),
			}]
		},
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/account-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/account-outline.svg`
		},
		openSearchKlantModal() {
			this.searchKlantModalOpen = true
		},
		closeSearchKlantModal() {
			this.searchKlantModalOpen = false
		},
		onShow(item) {
			klantStore.setWidgetKlantId(item.id)
			this.isModalOpen = true
			navigationStore.setModal('viewKlant')

		},
	},

}
</script>
<style scoped>
.personenContainer {
  display: flex;
  justify-content: space-between;
  flex-direction: column;
  height: 100%;
}
.itemContainer {
  overflow: auto;
  margin-block-end: var(--zaa-margin-10);
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
