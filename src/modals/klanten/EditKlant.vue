<script setup>
import { klantStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcDialog v-if="navigationStore.modal === 'editKlant'"
		name="Klant"
		size="normal"
		:can-close="false">
		<NcNoteCard v-if="success" type="success">
			<p>Klant succesvol aangepast</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success" class="form-group">
			<NcSelect
				v-bind="typeOptions"
				v-model="klantItem.type"
				:disabled="loading"
				input-label="Type klant"
				required />

			<NcTextField :disabled="loading"
				label="Voornaam"
				maxlength="255"
				:value.sync="klantItem.voornaam" />

			<NcTextField :disabled="loading"
				label="Tweede voornaam"
				maxlength="255"
				:value.sync="klantItem.tweedeVoornaam" />

			<NcTextField :disabled="loading"
				label="Tussenvoegsel"
				maxlength="255"
				:value.sync="klantItem.tussenvoegsel" />

			<NcTextField :disabled="loading"
				label="Achternaam"
				maxlength="255"
				:value.sync="klantItem.achternaam" />

			<NcTextField :disabled="loading"
				label="BSN"
				maxlength="255"
				:value.sync="klantItem.bsn" />
			<div>
				<p>Geboortedatum</p>
				<NcDateTimePicker v-model="klantItem.geboortedatum"
					:disabled="loading"
					input-label="Geboortedatum" />
			</div>

			<NcSelect v-bind="sexOptions"
				v-model="klantItem.geslacht"
				:clearable="false"
				class="wide-select"
				:disabled="loading"
				input-label="Geslacht" />

			<NcSelect
				v-bind="countryOptions"
				v-model="klantItem.land"
				class="wide-select"
				:disabled="loading"
				input-label="Land" />

			<NcTextField :disabled="loading"
				label="Telefoonnummer"
				maxlength="255"
				:value.sync="klantItem.telefoonnummer" />

			<NcTextField :disabled="loading"
				label="Email adres"
				maxlength="255"
				:value.sync="klantItem.emailadres" />

			<NcTextField :disabled="loading"
				label="Straatnaam"
				maxlength="255"
				:value.sync="klantItem.straatnaam" />

			<NcTextField :disabled="loading"
				label="Plaats"
				maxlength="255"
				:value.sync="klantItem.plaats" />

			<NcTextField :disabled="loading"
				label="Postcode"
				maxlength="255"
				:value.sync="klantItem.postcode" />

			<NcTextField :disabled="loading"
				label="Huisnummer"
				maxlength="255"
				:value.sync="klantItem.huisnummer" />

			<NcTextField :disabled="loading"
				label="Functie"
				maxlength="255"
				:value.sync="klantItem.functie" />

			<NcTextField :disabled="loading"
				label="Aanmaak kanaal"
				maxlength="255"
				:value.sync="klantItem.aanmaakkanaal" />

			<NcTextField :disabled="loading"
				label="Bron organisatie"
				maxlength="255"
				:value.sync="klantItem.bronorganisatie" />

			<NcTextField :disabled="loading"
				label="Bedrijfsnaam"
				maxlength="255"
				:value.sync="klantItem.bedrijfsnaam" />

			<NcTextField :disabled="loading"
				label="KVK nummer"
				maxlength="255"
				:value.sync="klantItem.kvkNummer" />

			<NcTextField :disabled="loading"
				label="Website Url"
				maxlength="255"
				:value.sync="klantItem.websiteUrl" />

			<NcTextField :disabled="loading"
				label="Url"
				maxlength="255"
				:value.sync="klantItem.url" />

			<NcTextField :disabled="loading"
				label="Geverifieerd"
				maxlength="255"
				:value.sync="klantItem.geverifieerd" />

			<NcTextField :disabled="loading"
				label="Subject"
				maxlength="255"
				:value.sync="klantItem.subject" />

			<NcTextField :disabled="loading"
				label="Subject Identificatie"
				maxlength="255"
				:value.sync="klantItem.subjectIdentificatie" />

			<NcTextField :disabled="loading"
				label="Subject Type"
				maxlength="255"
				:value.sync="klantItem.subjectType" />
		</div>

		<template #actions>
			<NcButton @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success ? 'Sluiten' : 'Annuleer' }}
			</NcButton>
			<NcButton @click="openLink('https://conduction.gitbook.io/opencatalogi-nextcloud/gebruikers/publicaties', '_blank')">
				<template #icon>
					<Help :size="20" />
				</template>
				Help
			</NcButton>
			<NcButton v-if="!success"
				:disabled="loading || !klantItem.type"
				type="primary"
				@click="editKlant()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading && klantStore.klantItem?.id" :size="20" />
					<Plus v-if="!loading && !klantStore.klantItem?.id" :size="20" />
				</template>
				{{ klantStore.klantItem?.id ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import {
	NcButton,
	NcDialog,
	NcTextField,
	NcLoadingIcon,
	NcNoteCard,
	NcSelect,
	NcDateTimePicker,
} from '@nextcloud/vue'
import { countries } from '../../data/countries.js'

// Icons
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Help from 'vue-material-design-icons/Help.vue'

export default {
	name: 'EditKlant',
	components: {
		NcDialog,
		NcTextField,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcSelect,
		NcDateTimePicker,
		// Icons
		ContentSaveOutline,
		Cancel,
		Plus,
		Help,
	},
	data() {
		return {
			success: false,
			loading: false,
			error: false,
			hasUpdated: false,
			countryOptions: {
				options: countries.map((country) => ({
					id: country.code,
					label: country.name,
				})),
			},
			klantItem: {
				voornaam: '',
				tweedeVoornaam: '',
				type: 'persoon',
				tussenvoegsel: '',
				achternaam: '',
				bsn: '',
				geboortedatum: '',
				geslacht: '',
				land: '',
				telefoonnummer: '',
				emailadres: '',
				straatnaam: '',
				plaats: '',
				postcode: '',
				huisnummer: '',
				functie: '',
				aanmaakkanaal: '',
				bronorganisatie: '',
				bedrijfsnaam: '',
				kvkNummer: '',
				websiteUrl: '',
				url: '',
				geverifieerd: '',
				subject: '',
				subjectIdentificatie: '',
				subjectType: '',
			},
			typeOptions: {
				options: [
					{ value: 'persoon', label: 'Persoon' },
					{ value: 'organisatie', label: 'Organisatie' },
				],
			},
			sexOptions: {
				options: [
					{ value: 'man', label: 'Man' },
					{ value: 'vrouw', label: 'Vrouw' },
					{ value: 'overige', label: 'Overige' },
				],
			},
		}
	},
	computed: {
		items() {
			return this.contactMomentItems
		},
	},
	updated() {
		if (navigationStore.modal === 'editKlant' && !this.hasUpdated) {
			const klantType = this.typeOptions.options.find((option) => option.value === klantStore.klantItem?.type)

			const country = this.countryOptions.options.find((option) => option.id === klantStore.klantItem?.land)

			const sex = this.sexOptions.options.find((option) => option.value === klantStore.klantItem?.geslacht)

			if (klantStore.klantItem?.id) {
				this.klantItem = {
					...klantStore.klantItem,
					voornaam: klantStore.klantItem.voornaam || '',
					tweedeVoornaam: klantStore.klantItem.tweedeVoornaam || '',
					type: klantType || { value: 'persoon', label: 'Persoon' },
					tussenvoegsel: klantStore.klantItem.tussenvoegsel || '',
					achternaam: klantStore.klantItem.achternaam || '',
					bsn: klantStore.klantItem.bsn || '',
					geboortedatum: klantStore.klantItem.geboortedatum ? new Date(klantStore.klantItem.geboortedatum) : '',
					geslacht: sex || { value: 'man', label: 'Man' },
					land: country || '',
					telefoonnummer: klantStore.klantItem.telefoonnummer || '',
					emailadres: klantStore.klantItem.emailadres || '',
					straatnaam: klantStore.klantItem.straatnaam || '',
					plaats: klantStore.klantItem.plaats || '',
					postcode: klantStore.klantItem.postcode || '',
					huisnummer: klantStore.klantItem.huisnummer || '',
					functie: klantStore.klantItem.functie || '',
					aanmaakkanaal: klantStore.klantItem.aanmaakkanaal || '',
					bronorganisatie: klantStore.klantItem.bronorganisatie || '',
					bedrijfsnaam: klantStore.klantItem.bedrijfsnaam || '',
					kvkNummer: klantStore.klantItem.kvkNummer || '',
					websiteUrl: klantStore.klantItem.websiteUrl || '',
					url: klantStore.klantItem.url || '',
					geverifieerd: klantStore.klantItem.geverifieerd || '',
					subject: klantStore.klantItem.subject || '',
					subjectIdentificatie: klantStore.klantItem.subjectIdentificatie || '',
					subjectType: klantStore.klantItem.subjectType || '',
				}
			}
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			this.success = false
			this.loading = false
			this.error = false
			this.hasUpdated = false
			this.klantItem = {
				voornaam: '',
				tweedeVoornaam: '',
				type: { value: 'persoon', label: 'Persoon' },
				tussenvoegsel: '',
				achternaam: '',
				bsn: '',
				geboortedatum: '',
				geslacht: { value: 'man', label: 'Man' },
				land: '',
				telefoonnummer: '',
				emailadres: '',
				straatnaam: '',
				plaats: '',
				postcode: '',
				huisnummer: '',
				functie: '',
				aanmaakkanaal: '',
				bronorganisatie: '',
				bedrijfsnaam: '',
				kvkNummer: '',
				websiteUrl: '',
				url: '',
				geverifieerd: '',
				subject: '',
				subjectIdentificatie: '',
				subjectType: '',
			}
		},
		async editKlant() {
			this.loading = true

			try {
				await klantStore.saveKlant({
					...this.klantItem,
					type: this.klantItem.type.value,
					geboortedatum: this.klantItem.geboortedatum !== '' && new Date(this.klantItem.geboortedatum).toISOString(),
					land: this.klantItem.land.id,
					geslacht: this.klantItem.geslacht.value,
				})
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the klant'
			}
		},
		openLink(url, target) {
			window.open(url, target)
		},
	},
}
</script>

<style scoped>
.wide-select {
	width: 100%;
}
</style>
