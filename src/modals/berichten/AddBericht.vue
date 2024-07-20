<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addBericht'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Bericht aanmaken</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>
			<div class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="onderwerp"
					label="Onderwerp" />

				<NcTextArea
					:disabled="loading"
					:value.sync="berichttekst"
					label="Berichttekst" />

				<NcTextArea
					:disabled="loading"
					:value.sync="inhoud"
					label="Inhoud (base64)" />

				<NcTextField
					:disabled="loading"
					:value.sync="bijlageType"
					label="Bijlage type" />

				<NcTextField
					:disabled="loading"
					:value.sync="soortGebruiker"
					label="Soort gebruiker" />

				<NcTextField
					:disabled="loading"
					:value.sync="publicatieDatum"
					label="Publicatiedatum" />

				<NcTextField
					:disabled="loading"
					:value.sync="aanmaakDatum"
					label="Aanmaak datum" />

				<NcTextField
					:disabled="loading"
					:value.sync="berichtType"
					label="Bericht type" />

				<NcTextField
					:disabled="loading"
					:value.sync="referentie"
					label="Referentie" />

				<NcTextField
					:disabled="loading"
					:value.sync="berichtID"
					label="Bericht ID" />

				<NcTextField
					:disabled="loading"
					:value.sync="batchID"
					label="Batch ID" />

				<NcTextField
					:disabled="loading"
					:value.sync="gebruikerID"
					label="Gebruiker ID" />

				<NcTextField
					:disabled="loading"
					:value.sync="volgorde"
					label="Volgorde" />
			</div>

			<NcButton
				v-if="!succes"
				:disabled="!store.attachmentItem.title || loading"
				type="primary"
				@click="addAttachment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Plus v-if="!loading" :size="20" />
				</template>
				Toevoegen
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcTextArea } from '@nextcloud/vue'

export default {
	name: 'AddBericht',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	methods: {
		addBericht() {
			this.$emit('bericht', this.onderwerp)
			fetch(
				'/index.php/apps/zaakafhandelapp/berichten/api',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						batchID: this.batchID,
						berichtID: this.berichtID,
						berichtType: this.berichtType,
						publicatieDatum: this.publicatieDatum,
						onderwerp: this.onderwerp,
						berichttekst: this.berichttekst,
						referentie: this.referentie,
						gebruikerID: this.gebruikerID,
						inhoud: this.inhoud,
						soortGebruiker: this.soortGebruiker,
						bijlageType: this.bijlageType,
						omschrijving: this.omschrijving,
						volgorde: this.volgorde,
					}),
				},
			)
				.then((response) => {
					this.succesMessage = true
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
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
