<script setup>
import { medewerkerStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcDialog v-if="navigationStore.modal === 'editMedewerker'"
		name="Medewerker"
		size="normal"
		:can-close="false">
		<NcNoteCard v-if="success" type="success">
			<p>Medewerker succesvol aangepast</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success" class="form-group">
			<NcTextField :disabled="loading"
				label="Voornaam"
				maxlength="255"
				:value.sync="medewerkerItem.voornaam" />

			<NcTextField :disabled="loading"
				label="Tussenvoegsel"
				maxlength="255"
				:value.sync="medewerkerItem.tussenvoegsel" />

			<NcTextField :disabled="loading"
				label="Achternaam"
				maxlength="255"
				:value.sync="medewerkerItem.achternaam" />

			<NcTextField :disabled="loading"
				label="Email adres"
				maxlength="255"
				:value.sync="medewerkerItem.emailadres" />
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
				:disabled="loading"
				type="primary"
				@click="editMedewerker()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading && medewerkerStore.medewerkerItem?.id" :size="20" />
					<Plus v-if="!loading && !medewerkerStore.medewerkerItem?.id" :size="20" />
				</template>
				{{ medewerkerStore.medewerkerItem?.id ? 'Opslaan' : 'Aanmaken' }}
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
} from '@nextcloud/vue'
import { countries } from '../../data/countries.js'

// Icons
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Help from 'vue-material-design-icons/Help.vue'

export default {
	name: 'EditMedewerker',
	components: {
		NcDialog,
		NcTextField,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
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
			medewerkerItem: {
				voornaam: '',
				tussenvoegsel: '',
				achternaam: '',
				email: '',
			},
		}
	},
	computed: {
		items() {
			return this.medewerkerItems
		},
	},
	updated() {
		if (navigationStore.modal === 'editMedewerker' && !this.hasUpdated) {

			if (medewerkerStore.medewerkerItem?.id) {
				this.medewerkerItem = {
					...medewerkerStore.medewerkerItem,
					voornaam: medewerkerStore.medewerkerItem.voornaam || '',
					tussenvoegsel: medewerkerStore.medewerkerItem.tussenvoegsel || '',
					achternaam: medewerkerStore.medewerkerItem.achternaam || '',
					email: medewerkerStore.medewerkerItem.email || '',
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
			this.medewerkerItem = {
				voornaam: '',
				tussenvoegsel: '',
				achternaam: '',
				email: '',
			}
		},
		async editMedewerker() {
			this.loading = true

			try {
				await medewerkerStore.saveMedewerker({
					...this.medewerkerItem,
				})
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the medewerker'
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
