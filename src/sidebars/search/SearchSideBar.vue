<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcAppSidebar
		v-model:active="activeTab"
		:name="t('zaakafhandelapp', 'Search')"
		:subname="t('zaakafhandelapp', 'Search within your municipality')">
		<NcAppSidebarTab id="search-tab"
			:name="t('zaakafhandelapp', 'Search')"
			:order="1">
			<template #icon>
				<Magnify :size="20" />
			</template>
			<NcTextField v-model="klantenSearch"
				:label="t('zaakafhandelapp', 'Search')" />

			<div v-if="klantenList && !loading">
				<NcListItem v-for="(klant, i) in klantenList"
					:key="`${klant}${i}`"
					:name="getName(klant)"
					:active="klantStore.klantItem.id === klant?.id"
					:force-display-actions="true"
					:details="_.upperFirst(klant.type)"
					:loading="loading">
					<template #icon>
						<AccountOutline :class="klantStore.klantItem === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ getSubname(klant) }}
					</template>
					<template #actions>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setSelected('klanten')">
							<template #icon>
								<Eye :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'View') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setDialog('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Delete') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</div>

			<NcLoadingIcon v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				:name="t('zaakafhandelapp', 'Loading customers')" />
		</NcAppSidebarTab>

		<NcAppSidebarTab id="personen-tab"
			:name="t('zaakafhandelapp', 'Persons')"
			:order="2">
			<template #icon>
				<AccountGroupOutline :size="20" />
			</template>

			<NcCheckboxRadioSwitch v-model="klantenSearchType"
				value="voornaam"
				name="klantenSearchType"
				type="radio">
				{{ t('zaakafhandelapp', 'First name') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch v-model="klantenSearchType"
				disabled
				value="geboortedatum_achternaam"
				name="klantenSearchType"
				type="radio">
				{{ t('zaakafhandelapp', 'Date of birth + last name') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch v-model="klantenSearchType"
				disabled
				value="postcode_huisnummer"
				name="klantenSearchType"
				type="radio">
				{{ t('zaakafhandelapp', 'Postal code + house number') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch v-model="klantenSearchType"
				value="emailadres"
				name="klantenSearchType"
				type="radio">
				{{ t('zaakafhandelapp', 'Email address') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch v-model="klantenSearchType"
				value="telefoonnummer"
				name="klantenSearchType"
				type="radio">
				{{ t('zaakafhandelapp', 'Phone number') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch v-model="klantenSearchType"
				value="bsn"
				name="klantenSearchType"
				type="radio">
				{{ t('zaakafhandelapp', 'BSN') }}
			</NcCheckboxRadioSwitch>

			<NcTextField v-model="personenSearch"
				:label="t('zaakafhandelapp', 'Search')" />

			<div v-if="klantenList && !personenLoading && !loading">
				<NcListItem v-for="(klant, i) in klantenList"
					:key="`${klant}${i}`"
					:name="getName(klant)"
					:active="klantStore.klantItem.id === klant?.id"
					:force-display-actions="true"
					:details="_.upperFirst(klant.type)"
					:loading="loading">
					<template #icon>
						<AccountOutline :class="klantStore.klantItem === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ getSubname(klant) }}
					</template>
					<template #actions>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setSelected('klanten')">
							<template #icon>
								<Eye :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'View') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setDialog('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Delete') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</div>

			<NcLoadingIcon v-if="personenLoading || loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				:name="t('zaakafhandelapp', 'Loading customers')" />
		</NcAppSidebarTab>

		<NcAppSidebarTab id="organisaties-tab"
			:name="t('zaakafhandelapp', 'Organisations')"
			:order="3">
			<template #icon>
				<OfficeBuildingOutline :size="20" />
			</template>
			<NcTextField v-model="organisatiesSearch"
				:label="t('zaakafhandelapp', 'Search')" />

			<div v-if="klantenList && !organisatiesLoading && !loading">
				<NcListItem v-for="(klant, i) in klantenList"
					:key="`${klant}${i}`"
					:name="getName(klant)"
					:active="klantStore.klantItem.id === klant?.id"
					:force-display-actions="true"
					:details="_.upperFirst(klant.type)"
					:loading="loading">
					<template #icon>
						<AccountOutline :class="klantStore.klantItem === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ getSubname(klant) }}
					</template>
					<template #actions>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setSelected('klanten')">
							<template #icon>
								<Eye :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'View') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setDialog('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Delete') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</div>

			<NcLoadingIcon v-if="organisatiesLoading || loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				:name="t('zaakafhandelapp', 'Loading customers')" />
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
<script>

import {
	NcAppSidebar,
	NcAppSidebarTab,
	NcTextField,
	NcCheckboxRadioSwitch,
	NcListItem,
	NcActionButton,
	NcLoadingIcon,
} from '@nextcloud/vue'
import _ from 'lodash'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'SearchSideBar',
	components: {
		NcAppSidebar,
		NcAppSidebarTab,
		NcTextField,
		NcCheckboxRadioSwitch,
		NcListItem,
		NcActionButton,
		NcLoadingIcon,
		// Icons
		Magnify,
		AccountGroupOutline,
		OfficeBuildingOutline,
	},
	data() {
		return {
			starred: false,
			loading: false,
			personenLoading: false,
			organisatiesLoading: false,
			klantenSearch: '',
			personenSearch: '',
			organisatiesSearch: '',
			klantenSearchType: 'voornaam',
			klantenList: [],
			debouncedFetchKlanten: null, // this is a function
			activeTab: 'search-tab',
		}
	},
	watch: {
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		klantenSearch() {
			this.debouncedFetchKlanten()
		},
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		personenSearch() {
			this.debouncedFetchKlanten()
		},
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		organisatiesSearch() {
			this.debouncedFetchKlanten()
		},
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		activeTab() {
			this.fetchKlanten()
		},
	},
	mounted() {
		this.fetchKlanten()
	},
	/**
	 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
	 */
	created() {
		// Initialize the debounced function
		this.debouncedFetchKlanten = this.debounce(() => this.fetchKlanten(), 100)
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		debounce(func, timeout = 300) {
			let timer
			return (...args) => {
				clearTimeout(timer)
				timer = setTimeout(() => { func.apply(this, args) }, timeout)
			}
		},
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
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
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		getSubname(klant) {
			if (klant.type === 'persoon') {
				return klant?.tussenvoegsel ? `${klant.tussenvoegsel} ${klant.achternaam}` : klant?.achternaam ? `${klant.achternaam}` : 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.websiteUrl ?? 'onbekend'
			}
			return 'onbekend'
		},
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-001
		 */
		fetchKlanten() {
			this.loading = true
			let activeFilter = null
			let searchParam = null

			switch (this.activeTab) {
			case 'personen-tab':
				activeFilter = 'persoon'
				searchParam = this.personenSearch
				this.personenLoading = true
				break
			case 'organisaties-tab':
				activeFilter = 'organisatie'
				searchParam = this.organisatiesSearch
				this.organisatiesLoading = true
				break
			default:
				searchParam = this.klantenSearch
				break
			}

			const searchParams = new URLSearchParams({
				...(searchParam && { [this.klantenSearchType]: searchParam }),
				...(activeFilter && { type: activeFilter }),
			}).toString()

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten?${searchParams}`)
				.then(response => response.json())
				.then(data => {
					this.klantenList = data?.results || []
				})
				.finally(() => {
					switch (this.activeTab) {
					case 'personen-tab':
						this.personenLoading = false
						this.loading = false

						break
					case 'organisaties-tab':
						this.organisatiesLoading = false
						this.loading = false

						break
					default:
						this.loading = false
						break
					}
				})
		},
	},
}
</script>
