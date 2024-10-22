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
			<NcTextField :disabled="loading"
				label="Voornaam"
				maxlength="255"
				:value.sync="klantItem.voornaam" />

			<NcTextField :disabled="loading"
				label="Tussenvoegsel"
				maxlength="255"
				:value.sync="klantItem.tussenvoegsel" />

			<NcTextField :disabled="loading"
				label="Achternaam"
				maxlength="255"
				:value.sync="klantItem.achternaam" />

			<NcTextField :disabled="loading"
				label="Telefoonnummer"
				maxlength="255"
				:value.sync="klantItem.telefoonnummer" />

			<NcTextField :disabled="loading"
				label="Email adres"
				maxlength="255"
				:value.sync="klantItem.emailadres" />

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
				:disabled="loading"
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
} from '@nextcloud/vue'
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
			klantItem: {
				voornaam: '',
				tussenvoegsel: '',
				achternaam: '',
				telefoonnummer: '',
				emailadres: '',
				functie: '',
				aanmaakkanaal: '',
				bronorganisatie: '',
				bedrijfsnaam: '',
				websiteUrl: '',
				url: '',
				geverifieerd: '',
				subject: '',
				subjectIdentificatie: '',
				subjectType: '',
			},
		}
	},
	updated() {
		if (navigationStore.modal === 'editKlant' && !this.hasUpdated) {
			if (klantStore.klantItem?.id) {
				this.klantItem = {
					...klantStore.klantItem,
					voornaam: klantStore.klantItem.voornaam || '',
					tussenvoegsel: klantStore.klantItem.tussenvoegsel || '',
					achternaam: klantStore.klantItem.achternaam || '',
					telefoonnummer: klantStore.klantItem.telefoonnummer || '',
					emailadres: klantStore.klantItem.emailadres || '',
					functie: klantStore.klantItem.functie || '',
					aanmaakkanaal: klantStore.klantItem.aanmaakkanaal || '',
					bronorganisatie: klantStore.klantItem.bronorganisatie || '',
					bedrijfsnaam: klantStore.klantItem.bedrijfsnaam || '',
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
				tussenvoegsel: '',
				achternaam: '',
				telefoonnummer: '',
				emailadres: '',
				functie: '',
				aanmaakkanaal: '',
				bronorganisatie: '',
				bedrijfsnaam: '',
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

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

/* .modal__content > button {
    margin-block: 6px;
} */

.zaakDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}

/* .input-field__label {
    margin-block: -6px;
}
.input-field__input:focus + .input-field__label {
    margin-block: 0px;
} */
</style>
