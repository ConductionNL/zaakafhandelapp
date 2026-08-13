<script setup>
import { translate as t } from '@nextcloud/l10n'
import { klantStore } from '../../store/store.js'
</script>

<template>
	<NcDialog
		:name="
			startingType === 'persoon'
				? t('zaakafhandelapp', 'Search person')
				: t('zaakafhandelapp', 'Search organisation')
		"
		size="normal"
		label-id="searchKlantModal"
		dialog-classes="SearchKlantModal"
		:close-on-click-outside="false"
		@closing="closeModalFromButton()">
		<div class="listContainer">
			<div class="filtersContainer">
				<NcCheckboxRadioSwitch
					v-if="startingType === 'persoon'"
					v-model="klantenSearchType"
					value="geboortedatum_achternaam"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'Date of birth + last name') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-if="startingType === 'persoon'"
					v-model="klantenSearchType"
					value="bsn"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'BSN') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-if="startingType === 'organisatie'"
					v-model="klantenSearchType"
					value="bedrijfsnaam"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'Company name') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-if="startingType === 'organisatie'"
					v-model="klantenSearchType"
					value="kvkNummer"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'Chamber of commerce number') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-model="klantenSearchType"
					value="postcode_huisnummer"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'Postal code + house number') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-model="klantenSearchType"
					value="emailadres"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'Email address') }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-model="klantenSearchType"
					value="telefoonnummer"
					name="klantenSearchType"
					type="radio">
					{{ t('zaakafhandelapp', 'Phone number') }}
				</NcCheckboxRadioSwitch>
			</div>
		</div>

		<div class="searchContainer">
			<div
				v-if="klantenSearchType === 'geboortedatum_achternaam'"
				class="flex">
				<NcDateTimePicker
					v-model="searchQuery_geboortedatum"
					:disabled="loading"
					class="date-picker" />

				<NcTextField
					v-model="searchQuery"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Last name')"
					maxlength="255"
					class="searchField" />
			</div>
			<div v-else>
				<NcTextField
					v-model="searchQuery"
					:disabled="loading"
					:label="searchLabel"
					maxlength="255"
					class="searchField" />
			</div>

			<NcButton
				variant="primary"
				:disabled="
					loading
					|| !searchQuery
					// If the search type is geboortedatum_achternaam, the geboortedatum is required
					|| (klantenSearchType === 'geboortedatum_achternaam'
						&& !searchQuery_geboortedatum)
				"
				class="searchButton"
				@click="search">
				<template #icon>
					<Search :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Search') }}
			</NcButton>
		</div>

		<div class="searchResultsContainer">
			<div v-if="klanten?.length && !loading">
				<NcListItem
					v-for="(klant, i) in klanten"
					:key="`${klant}${i}`"
					:name="`${getSex(klant)} ${getName(klant)} ${getSubname(klant)}`"
					:active="selectedKlant === klant?.id"
					:force-display-actions="true"
					:details="_.upperFirst(klant.type)"
					@click="setActive(klant.id)">
					<template #icon>
						<OfficeBuildingOutline
							v-if="klant.type === 'organisatie'"
							:class="selectedKlant === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
						<AccountOutline
							v-if="klant.type === 'persoon'"
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
				{{
					startingType === 'persoon'
						? t('zaakafhandelapp', 'No persons found.')
						: t('zaakafhandelapp', 'No organisations found')
				}}
			</div>

			<NcLoadingIcon
				v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				:name="t('zaakafhandelapp', 'Searching')" />
		</div>

		<template #actions>
			<NcButton variant="secondary" @click="closeModal()">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Cancel') }}
			</NcButton>
			<NcButton
				variant="primary"
				:disabled="!selectedKlant"
				@click="addKlant()">
				<template #icon>
					<Plus :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Link') }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
// Components
import {
	NcButton,
	NcTextField,
	NcDialog,
	NcListItem,
	NcLoadingIcon,
	NcCheckboxRadioSwitch,
	NcDateTimePicker,
} from '@nextcloud/vue'
import { getTheme } from '../../services/getTheme.js'
import _ from 'lodash'

// Icons
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
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
		NcDateTimePicker,
		NcLoadingIcon,
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
			searchQuery_geboortedatum: null,
			selectedKlant: null,
			klantenSearchType: 'emailadres',
		}
	},
	computed: {
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-004
		 */
		searchLabel() {
			const typeLabels = {
				persoon: {
					default: t('zaakafhandelapp', 'person'),
					geboortedatum_achternaam: t(
						'zaakafhandelapp',
						'with date of birth and last name',
					),
					bsn: t('zaakafhandelapp', 'with BSN'),
				},
				organisatie: {
					default: t('zaakafhandelapp', 'organisation'),
					bedrijfsnaam: t('zaakafhandelapp', 'with company name'),
					kvkNummer: t(
						'zaakafhandelapp',
						'with chamber of commerce number',
					),
				},
			}
			const commonLabels = {
				postcode_huisnummer: t(
					'zaakafhandelapp',
					'with postal code and house number',
				),
				emailadres: t('zaakafhandelapp', 'with email address'),
				telefoonnummer: t('zaakafhandelapp', 'with phone number'),
			}

			let label =
				t('zaakafhandelapp', 'Search for a')
				+ ' '
				+ (typeLabels[this.startingType]?.default || '')
			const modifier =
				typeLabels[this.startingType]?.[this.klantenSearchType]
				|| commonLabels[this.klantenSearchType]
				|| ''
			if (modifier) label += ' ' + modifier

			return label
		},
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModal() {
			this.$emit('close-modal')
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-004
		 */
		addKlant() {
			// eslint-disable-next-line no-console
			console.log('added')
			this.$emit('selected-klant', this.selectedKlant)
			this.closeModalFromButton()
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-004
		 */

		search() {
			this.loading = true

			this.selectedKlant = null

			let queryParams = { [this.klantenSearchType]: this.searchQuery }
			const newQuery = this.searchQuery.trim()
			const splitQuery = newQuery.split(/ +/g)

			switch (this.klantenSearchType) {
				case 'postcode_huisnummer':
					queryParams = {
						postcode: splitQuery[0],
						huisnummer: splitQuery[1],
					}
					break
				case 'geboortedatum_achternaam':
					queryParams = {
						geboortedatum:
							this.searchQuery_geboortedatum
							&& this.searchQuery_geboortedatum.toISOString()
								? this.searchQuery_geboortedatum.toISOString()
								: '',
						achternaam: newQuery,
					}
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

			klantStore.searchKlanten(searchParams).then(() => {
				this.klanten = klantStore.klantenList
				this.loading = false
			})
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light'
				? `${appLocation}/zaakafhandelapp/img/office-building-outline-dark.svg`
				: `${appLocation}/zaakafhandelapp/img/office-building-outline.svg`
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */

		getName(klant) {
			if (klant.type === 'persoon') {
				return klant?.voornaam ?? 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.bedrijfsnaam ?? 'onbekend'
			}
			return 'onbekend'
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */
		getSubname(klant) {
			if (klant.type === 'persoon') {
				return klant?.tussenvoegsel
					? `${klant.tussenvoegsel} ${klant.achternaam}`
					: klant?.achternaam
						? `${klant.achternaam}`
						: 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return ''
			}
			return 'onbekend'
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */
		getSummary(klant) {
			if (klant.type === 'persoon') {
				const geboortedatum = getValidISOstring(klant.geboortedatum)
					? new Date(klant.geboortedatum).toLocaleDateString()
					: 'onbekend'
				const geboortestad = klant.plaats ? `${klant.plaats}` : 'onbekend'
				return `${geboortedatum} - ${geboortestad}`
			}
			if (klant.type === 'organisatie') {
				return klant?.websiteUrl ?? 'onbekend'
			}
			return 'onbekend'
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */
		getSex(klant) {
			if (klant.type === 'persoon') {
				return `(${klant?.geslacht})`
			}
			return ''
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-002
		 */
		setActive(klant) {
			if (this.selectedKlant === klant) {
				this.selectedKlant = null
			} else {
				this.selectedKlant = klant
			}
		},
	},
}
</script>

<style scoped>
.listContainer {
	margin-bottom: 10px;
}

.filtersContainer {
	display: ruby;
}

.searchContainer {
	display: flex;
	align-items: center;
	gap: 10px;
}
.searchField {
	width: auto;
}
.searchButton {
	margin-block-start: 3px;
	min-width: min-content !important;
}

.searchResultsContainer {
	border-top: 1px solid black;
	border-bottom: 1px solid black;
	padding-block: 20px;
	margin-block: 30px;
}

.flex {
	display: flex;
	gap: 10px;
}

.date-picker {
	margin-block-start: 3px;
}
</style>
