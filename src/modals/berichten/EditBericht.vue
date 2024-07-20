<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editBericht'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Bericht aanpassen</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Onderwerp"
					:value.sync="bericht.onderwerp" />
			</div>
			<div class="form-group">
				<NcTextArea label="Berichttekst"
					:value.sync="bericht.berichttekst" />
			</div>
			<div class="form-group">
				<NcTextArea
					:disabled="loading"
					label="Inhoud (base64)"
					:value.sync="bericht.inhoud" />
			</div>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Bijlage type"
					:value.sync="bericht.bijlageType" />
			</div>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Soort gebruiker"
					:value.sync="bericht.soortGebruiker" />
			</div>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Publicatiedatum"
					:value.sync="bericht.publicatieDatum" />
			</div>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Aanmaak datum"
					:value.sync="bericht.aanmaakDatum" />
			</div>
			<div class="form-group">
				<NcTextField label="Bericht type"
					:value.sync="bericht.berichtType"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Referentie"
					:value.sync="bericht.referentie" />
			</div>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					label="Bericht ID"
					:value.sync="bericht.berichtID" />

				<NcTextField
					:disabled="loading"
					label="Batch ID"
					:value.sync="bericht.batchID" />

				<NcTextField
					:disabled="loading"
					abel="Gebruiker ID"
					:value.sync="bericht.gebruikerID" />

				<NcTextField
					:disabled="loading"
					label="Volgorde"
					:value.sync="bericht.volgorde" />
			</div>

			<NcButton
				v-if="!succes"
				:disabled="!store.attachmentItem.title || loading"
				type="primary"
				@click="addAttachment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcLoadingIcon, NcModal, NcTextField, NcTextArea } from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditBericht',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcLoadingIcon,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (store.modal === 'editBericht' && this.hasUpdated) {
			if (this.bericht === store.berichtItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editBericht' && !this.hasUpdated) {
			this.hasUpdated = true
			this.bericht = store.berichtItem
		}
	},
	methods: {
		editBericht() {
			this.berichtLoading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/berichten/${store.berichtId}`,
				{
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(this.bericht),
				},
			).then((response) => {
				this.berichtLoading = false
				this.succesMessage = true
				setTimeout(() => (this.succesMessage = false), 2500)
			}).catch((err) => {
				this.berichtLoading = false
				console.error(err)
			})
		},
	},
}
</script>

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

.berichtDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}
</style>
