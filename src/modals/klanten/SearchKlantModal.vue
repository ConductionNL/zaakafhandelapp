<script setup>
import { klantStore } from '../../store/store.js'
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
			<div class="filtersContainer">
				<NcCheckboxRadioSwitch v-if="startingType === 'persoon'"
					:checked.sync="klantenSearchType"
					disabled
					value="geboortedatum_achternaam"
					name="klantenSearchType"
					type="radio">
					Geboortedatum + achternaam
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch v-if="startingType === 'persoon'"
					:checked.sync="klantenSearchType"
					value="bsn"
					name="klantenSearchType"
					type="radio">
					BSN
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch v-if="startingType === 'organisatie'"
					:checked.sync="klantenSearchType"
					value="bedrijfsnaam"
					name="klantenSearchType"
					type="radio">
					Bedrijfsnaam
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch v-if="startingType === 'organisatie'"
					:checked.sync="klantenSearchType"
					value="kvkNummer"
					name="klantenSearchType"
					type="radio">
					KVK nummer
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
					value="postcode_huisnummer"
					name="klantenSearchType"
					type="radio">
					Postcode + huisnummer
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
					value="emailadres"
					name="klantenSearchType"
					type="radio">
					Emailadres
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
					value="telefoonnummer"
					name="klantenSearchType"
					type="radio">
					Telefoonnummer
				</NcCheckboxRadioSwitch>
			</div>
		</div>

		<div class="searchContainer">
			<NcTextField :disabled="loading"
				:label="startingType === 'persoon' ? 'Zoek naar een persoon' : 'Zoek naar een organisatie'"
				maxlength="255"
				class="searchField"
				:value.sync="searchQuery" />

			<NcButton type="primary"
				:disabled="loading || !searchQuery"
				class="searchButton"
				@click="search">
				<template #icon>
					<Search :size="20" />
				</template>
				Zoeken
			</NcButton>
		</div>

		<div class="searchResultsContainer">
			<div v-if="klanten?.length && !loading">
				<NcListItem v-for="(klant, i) in klanten"
					:key="`${klant}${i}`"
					:name="`${getName(klant)} ${getSubname(klant)}`"
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
						{{ getSummary(klant) }}
					</template>
				</NcListItem>
			</div>

			<div v-if="!klanten?.length && !loading">
				Geen {{ startingType === 'persoon' ? 'personen' : 'organisaties' }} gevonden.
			</div>

			<NcLoadingIcon v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				name="Aan het zoeken" />
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
import { NcButton, NcTextField, NcDialog, NcListItem, NcLoadingIcon, NcCheckboxRadioSwitch } from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import _ from 'lodash'

// Icons
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Search from 'vue-material-design-icons/Magnify.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import getValidISOstring from '../../services/getValidISOstring.js'
export default {
	name: 'SearchKlantModal',
	components: {
		NcDialog,
		NcButton,
		NcListItem,
		OfficeBuildingOutline,
		AccountOutline,
		Search,
		NcCheckboxRadioSwitch,
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
			klantenSearchType: 'emailadres',
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

		search() {
			this.loading = true

			this.selectedKlant = null

			let queryParams = { [this.klantenSearchType]: this.searchQuery }
			const newQuery = this.searchQuery.replaceAll(' ', '')

			switch (this.klantenSearchType) {
			case 'postcode_huisnummer':
				queryParams = { postcode: newQuery.substring(0, 6), huisnummer: newQuery.substring(6) }
				break
			case 'kvkNummer':
				queryParams = { kvkNummer: newQuery }
				break
			default:
				break
			}

			const searchParams = new URLSearchParams({
				...(this.searchQuery && queryParams),
				...(this.startingType && { type: this.startingType }),
			}).toString()

			klantStore.searchKlanten(searchParams)
				.then(() => {
					console.log(klantStore.klantenList)
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
				return ''
			}
			return 'onbekend'
		},
		getSummary(klant) {
			if (klant.type === 'persoon') {
				const geboortedatum = getValidISOstring(klant.geboortedatum) ? new Date(klant.geboortedatum).toLocaleDateString() : 'onbekend'
				const geboortestad = klant.plaats ? `${klant.plaats}` : 'onbekend'
				return `${geboortedatum} - ${geboortestad}`
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

.filtersContainer {
	display: ruby;
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

  .searchResultsContainer {
    border-top: 1px solid black;
    border-bottom: 1px solid black;
    padding-block: 20px;
    margin-block: 30px;
  }
</style>
