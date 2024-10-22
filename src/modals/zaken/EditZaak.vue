<script setup>
import { zaakStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcDialog v-if="navigationStore.modal === 'addZaak'" 
		name="Zaak"
		size="normal"
		:can-close="false">
		<NcNoteCard v-if="success" type="success">
			<p>Zaak succesvol aangepast</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success" class="form-group">
			<NcTextField
				:disabled="loading"
				:value.sync="zaakItem.identificatie"
				label="Identificatie"
				maxlength="255"
				required />

			<NcTextField
				:disabled="loading"
				:value.sync="zaakItem.omschrijving"
				label="Omschrijving"
				maxlength="255" />

			<NcTextField
				:disabled="loading"
				:value.sync="zaakItem.bronorganisatie"
				label="Bron organisatie"
				maxlength="9"
				required />

			<NcTextField
				:disabled="loading"
				:value.sync="zaakItem.verantwoordelijkeOrganisatie"
				label="Verantwoordelijke Organisatie"
				maxlength="9"
				required />

			<NcTextField
				:disabled="loading"
				:value.sync="zaakItem.startdatum"
				label="Startdatum"
				maxlength="9"
				required />

			<NcSelect
				v-bind="zaakItem.zaaktype"
				v-model="zaaktype.value"
				:disabled="loading"
				input-label="Zaaktype"
				required />

			<NcSelect
				:disabled="loading"
				:value.sync="zaakItem.Archiefstatus"
				input-label="Archiefstatus"
				required />

			<NcTextField
				:disabled="loading"
				:value.sync="zaakItem.registratiedatum"
				label="Registratiedatum"
				maxlength="255" />

			<NcTextArea
				:disabled="loading"
				:value.sync="zaakItem.toelichting"
				label="Toelichting" />
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
				@click="editZaak()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading && zaakStore.zaakItem?.id" :size="20" />
					<Plus v-if="!loading && !zaakStore.zaakItem?.id" :size="20" />
				</template>
				{{ zaakStore.zaakItem?.id ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import { NcButton, NcDialog, NcTextField, NcSelect, NcTextArea, NcLoadingIcon } from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Help from 'vue-material-design-icons/Help.vue'

export default {
	name: 'EditZaak',
	components: {
		NcDialog,
		NcTextField,
		NcButton,
		NcSelect,
		NcTextArea,
		NcLoadingIcon,
		// Icons
		ContentSaveOutline,
		Cancel,
		Plus,
		Help,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
			hasUpdated: false,
			zaakItem: {
				identificatie: '',
				omschrijving: '',
				bronorganisatie: '',
				verantwoordelijkeOrganisatie: '',
				startdatum: '',
				zaaktype: '',
				Archiefstatus: '',
				registratiedatum: '',
				toelichting: '',
			},
			// Select options
			zaakTypeLoading: false,
			zaaktype: false,
		}
	},
	updated() {
		if (navigationStore.modal === 'editZaak' && !this.hasUpdated) {
			if (zaakStore.zaakItem?.id) {
				this.zaakItem = {
					...zaakStore.zaakItem,
					identificatie: zaakStore.zaakItem.identificatie || '',
					omschrijving: zaakStore.zaakItem.omschrijving || '',
					bronorganisatie: zaakStore.zaakItem.bronorganisatie || '',
					verantwoordelijkeOrganisatie: zaakStore.zaakItem.verantwoordelijkeOrganisatie || '',
					startdatum: zaakStore.zaakItem.startdatum || '',
					zaaktype: zaakStore.zaakItem.zaaktype || '',
					Archiefstatus: zaakStore.zaakItem.Archiefstatus || '',
					registratiedatum: zaakStore.zaakItem.registratiedatum || '',
					toelichting: zaakStore.zaakItem.toelichting || '',
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
			this.zaakItem = {
				identificatie: '',
				omschrijving: '',
				bronorganisatie: '',
				verantwoordelijkeOrganisatie: '',
				startdatum: '',
				zaaktype: '',
				Archiefstatus: '',
				registratiedatum: '',
				toelichting: '',
			}
		},
		async editZaak() {
			this.loading = true
			try {
				await zaakStore.saveZaak({
					...this.zaakItem,
				})
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the zaak'
			}
		},
		openLink(url, target) {
			window.open(url, target)
		},
	},
}
</script>

<style>
.modalContent {
    margin: var(--zaa-margin-50);
    text-align: center;
}

</style>
