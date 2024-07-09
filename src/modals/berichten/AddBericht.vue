<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addBericht'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Bericht aanmaken</h2>
			<div class="form-group">
				<NcTextField label="Onderwerp" :value.sync="onderwerp" />
			</div>
			<div class="form-group">
				<NcTextArea label="Berichttekst" :value.sync="berichttekst" />
			</div>
			<div class="form-group">
				<NcTextArea label="Inhoud (base64)" :value.sync="inhoud" />
			</div>
			<div class="form-group">
				<NcTextField label="Bijlage type" :value.sync="bijlageType" />
			</div>
			<div class="form-group">
				<NcTextField label="Soort gebruiker" :value.sync="soortGebruiker" />
			</div>
			<div class="form-group">
				<NcTextField label="Publicatiedatum" :value.sync="publicatieDatum" />
			</div>
			<div class="form-group">
				<NcTextField label="Aanmaak datum" :value.sync="aanmaakDatum" />
			</div>
			<div class="form-group">
				<NcTextField label="Bericht type" :value.sync="berichtType" />
			</div>
			<div class="form-group">
				<NcTextField label="Referentie" :value.sync="referentie" />
			</div>
			<div class="form-group">
				<NcTextField label="Bericht ID" :value.sync="berichtID" />
			</div>
			<div class="form-group">
				<NcTextField label="Batch ID" :value.sync="batchID" />
			</div>
			<div class="form-group">
				<NcTextField label="Gebruiker ID" :value.sync="gebruikerID" />
			</div>
			<div class="form-group">
				<NcTextField label="Volgorde" :value.sync="volgorde" />
			</div>
			<div v-if="succesMessage" class="success">
				Bericht succesvol aangemaakt
			</div>

			<NcButton :disabled="!onderwerp" type="primary" @click="addBericht">
				Aanmaken
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
			batchID: '',
			berichtID: '',
			berichtType: '',
			publicatieDatum: new Date().toISOString().split('T')[0],
			onderwerp: '',
			berichttekst: '',
			referentie: '',
			gebruikerID: '',
			inhoud: '',
			soortGebruiker: 'Burger',
			bijlageType: 'Pdf',
			omschrijving: '',
			volgorde: '',
			succesMessage: false,
		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
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
