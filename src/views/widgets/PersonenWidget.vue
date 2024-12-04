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
			<NcTextField :disabled="loading"
				label="Zoeken op voornaam"
				maxlength="255"
				class="searchField"
				:value.sync="searchPerson" />

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
				@save-success="fetchPersonenItems" />
		</div>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import Search from 'vue-material-design-icons/Magnify.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import ViewKlantRegister from '../../modals/klantRegister/ViewKlantRegister.vue'

export default {
	name: 'PersonenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Search,
		NcTextField,
		AccountOutline,
		ViewKlantRegister,
		NcLoadingIcon,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			personenItems: [],
			searchPerson: '',
			selectedKlantId: '',
			itemMenu: {
				show: {
					text: 'Bekijk',
					icon: 'icon-toggle',
				},
			},
		}
	},

	methods: {
		search() {
			if (!this.searchPerson.trim()) {
				this.personenItems = []
				return
			}

			this.loading = true

			klantStore.searchPersons(this.searchPerson)
				.then(({ data }) => {
					this.personenItems = data.map(person => ({
						id: person.id,
						mainText: `${person.voornaam} ${person.tussenvoegsel} ${person.achternaam}`,
						subText: person.emailadres,
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

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/account-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/account-outline.svg`
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
