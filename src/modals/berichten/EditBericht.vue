<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editBericht'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Bericht aanpassen</h2>

			<div class="form-group">
				<NcTextField label="Onderwerp"
					:value.sync="bericht.onderwerp"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextArea label="Berichttekst"
					:value.sync="bericht.berichttekst"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextArea label="Inhoud (base64)"
					:value.sync="bericht.inhoud"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Bijlage type"
					:value.sync="bericht.bijlageType"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Soort gebruiker"
					:value.sync="bericht.soortGebruiker"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Publicatiedatum"
					:value.sync="bericht.publicatieDatum"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Aanmaak datum"
					:value.sync="bericht.aanmaakDatum"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Bericht type"
					:value.sync="bericht.berichtType"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Referentie"
					:value.sync="bericht.referentie"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Bericht ID"
					:value.sync="bericht.berichtID"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Batch ID"
					:value.sync="bericht.batchID"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Gebruiker ID"
					:value.sync="bericht.gebruikerID"
					:loading="berichtLoading" />
			</div>
			<div class="form-group">
				<NcTextField label="Volgorde"
					:value.sync="bericht.volgorde"
					:loading="berichtLoading" />
			</div>
			<div v-if="succesMessage" class="success">
				Bericht succesvol opgeslagen
			</div>
			<NcButton :disabled="!bericht.onderwerp" type="primary" @click="editBericht">
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcTextArea } from '@nextcloud/vue'

export default {
	name: 'EditBericht',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
	},
	data() {
		return {
			bericht: {
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
			},
			succesMessage: false,
			hasUpdated: false,
			berichtLoading: false,
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
		closeModal() {
			store.modal = false
		},
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
