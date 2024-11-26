<script setup>
import { navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcDialog
		name="Klanten zoeken"
		size="normal"
		label-id="searchKlantModal"
		dialog-classes="SearchKlantModal"
		:close-on-click-outside="false"
		@closing="closeModalFromButton()">
		<div class="listContainer">
			<div v-if="klanten?.length && !loading">
				<NcListItem v-for="(klant, i) in klanten"
					:key="`${klant}${i}`"
					:name="getName(klant)"
					:active="selectedKlant === klant?.id"
					:force-display-actions="true"
					:details="_.upperFirst(klant.type)"
					@click="setActive(klant.id)">
					<template #icon>
						<OfficeBuildingOutline v-if="klant.type === 'organisatie'"
							:class="selectedKlant === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
						<AccountOutline v-if="klant.type === 'persoon'"
							:class="selectedKlant === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ getSubname(klant) }}
					</template>
				</NcListItem>
			</div>

			<div v-if="!klanten?.length && !loading">
				Geen klanten gevonden.
			</div>

			<NcLoadingIcon v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				name="Aan het zoeken" />
		</div>

		<div class="searchContainer">
			<NcTextField :disabled="loading"
				label="Zoeken op bedrijfsnaam"
				maxlength="255"
				class="searchField"
				:value.sync="searchQuery" />

			<NcButton type="primary"
				:disabled="loading"
				class="searchButton"
				@click="searchFunction">
				<template #icon>
					<Search :size="20" />
				</template>
				Zoeken
			</NcButton>
		</div>
		<template #actions>
			<NcButton
				type="secondary"
				@click="closeModal()">
				<template #icon>
					<Cancel v-if="!loading" :size="20" />
				</template>
				Annuleer
			</NcButton>
			<NcButton
				type="primary"
				:disabled="!selectedKlant"
				@click="addKlant()">
				<template #icon>
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				toevoegen
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
// Components
import { NcButton, NcTextField, NcDialog, NcListItem, NcLoadingIcon } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import _ from 'lodash'

// Icons
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Search from 'vue-material-design-icons/Magnify.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
export default {
	name: 'SearchKlantModal',
	components: {
		NcDialog,
		NcButton,
		NcListItem,
		OfficeBuildingOutline,
		AccountOutline,
		Search,
	},
	props: {
		startingType: {
			type: String,
			required: false,
			default: 'all',
		},
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
			hasUpdated: false,
			klanten: [],
			searchQuery: '',
			selectedKlant: null,
		}
	},
	mounted() {
		this.searchFunction()
	},
	updated() {
		if (navigationStore.modal === 'searchKlant' && !this.hasUpdated) {

			switch (this.startingType) {
			case 'organisatie':
				this.searchOrganisations()
				break
			case 'persoon':
				this.searchPersons()
				break
			default:
				this.searchAll()
			}

			this.hasUpdated = true
		}
	},
	methods: {
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		closeModal() {
			this.$emit('close-modal')

		},
		addKlant() {
			// eslint-disable-next-line no-console
			console.log('added')
			this.$emit('selected-klant', this.selectedKlant)
			this.closeModalFromButton()
		},
		searchFunction() {
			switch (this.startingType) {
			case 'organisatie':
				this.searchOrganisations()
				break
			case 'persoon':
				this.searchPersons()
				break
			default:
				this.searchAll()
			}
		},
		searchAll() {
			this.loading = true
			klantStore.refreshKlantenList(this.searchQuery)
				.then(() => {
					this.klanten = klantStore.klantenList
					this.loading = false
				})
		},
		searchOrganisations() {
			this.loading = true
			klantStore.searchOrganisations(this.searchQuery)
				.then(() => {
					this.klanten = klantStore.klantenList
					this.loading = false
				})
				.finally(() => {
					this.loading = false
				})
		},
		searchPersons() {
			this.loading = true
			klantStore.searchPersons(this.searchQuery)
				.then(() => {
					this.klanten = klantStore.klantenList
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

		getName(klant) {
			if (klant.type === 'persoon') {
				return klant?.voornaam ?? 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.bedrijfsnaam ?? 'onbekend'
			}
			return 'onbekend'
		},
		getSubname(klant) {
			if (klant.type === 'persoon') {
				return klant?.tussenvoegsel ? `${klant.tussenvoegsel} ${klant.achternaam}` : klant?.achternaam ? `${klant.achternaam}` : 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.websiteUrl ?? 'onbekend'
			}
			return 'onbekend'
		},
		setActive(klant) {
			if (this.selectedKlant === klant) {
				this.selectedKlant = null
			} else { this.selectedKlant = klant }
		},
	},
}
</script>

<style>

</style>

<style scoped>
.listContainer {
	margin-bottom: 10px;
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
