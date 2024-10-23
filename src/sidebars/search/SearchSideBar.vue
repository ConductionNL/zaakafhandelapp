<script setup>
import { navigationStore, searchStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcAppSidebar
		name="Zoek opdracht"
		subname="Via deze pagina kunt u zoeken binnen uw gemeente">
		<NcAppSidebarTab id="search-tab" name="Zoeken" :order="1">
			<template #icon>
				<Magnify :size="20" />
			</template>
			<NcTextField class="searchField"
				:value.sync="searchStore.search"
				label="Search" />
		</NcAppSidebarTab>

		<NcAppSidebarTab id="share-tab" name="Personen" :order="2">
			<template #icon>
				<AccountGroupOutline :size="20" />
			</template>

			<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
				value="voornaam"
				name="klantenSearchType"
				type="radio">
				Voornaam
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
				disabled
				value="geboortedatum_achternaam"
				name="klantenSearchType"
				type="radio">
				Geboortedatum + achternaam
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
				disabled
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
			<NcCheckboxRadioSwitch :checked.sync="klantenSearchType"
				value="bsn"
				name="klantenSearchType"
				type="radio">
				BSN
			</NcCheckboxRadioSwitch>

			<NcTextField :value.sync="klantenSearch"
				label="Search" />

			<div v-if="klantenList">
				<NcListItem v-for="(klant, i) in klantenList"
					:key="`${klant}${i}`"
					:name="klant.voornaam || 'onbekend'"
					:active="klantStore.klantItem.id === klant?.id"
					:force-display-actions="true">
					<template #icon>
						<AccountOutline :class="klantStore.klantItem === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ klant?.voorvoegsel
							? `${klant.voorvoegsel} ${klant.achternaam}`
							: klant?.achternaam
								? `${klant.achternaam}`
								: 'onbekend'
						}}
					</template>
					<template #actions>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setSelected('klanten')">
							<template #icon>
								<Eye :size="20" />
							</template>
							View
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setDialog('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</NcAppSidebarTab>

		<NcAppSidebarTab id="settings-tab" name="Organisaties" :order="3">
			<template #icon>
				<OfficeBuildingOutline :size="20" />
			</template>
			<NcTextField class="searchField"
				:value.sync="searchStore.search"
				label="Naam" />
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
} from '@nextcloud/vue'
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
		// Icons
		Magnify,
		AccountGroupOutline,
		OfficeBuildingOutline,
	},
	data() {
		return {
			starred: false,
			klantenSearch: '',
			klantenSearchType: 'voornaam',
			klantenList: [],
			debouncedFetchKlanten: null, // this is a function
		}
	},
	watch: {
		klantenSearch() {
			this.debouncedFetchKlanten()
		},
	},
	mounted() {
		this.fetchKlanten()
	},
	created() {
		// Initialize the debounced function
		this.debouncedFetchKlanten = this.debounce(() => this.fetchKlanten(), 100)
	},
	methods: {
		debounce(func, timeout = 300) {
			let timer
			return (...args) => {
				clearTimeout(timer)
				timer = setTimeout(() => { func.apply(this, args) }, timeout)
			}
		},
		fetchKlanten() {
			const searchParams = new URLSearchParams({
				...(this.klantenSearch && { [this.klantenSearchType]: this.klantenSearch }),
			}).toString()

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten?${searchParams}`)
				.then(response => response.json())
				.then(data => {
					this.klantenList = data?.results || []
				})
		},
	},
}
</script>
