<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addKlant'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Klant toevoegen</h2>

			<div class="formContainer">
				<div class="form-group">
					<NcTextField :disabled="klantLoading"
						label="Voornaam"
						maxlength="255"
						:value.sync="klant.voornaam"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Tussenvoegsel"
						maxlength="255"
						:value.sync="klant.tussenvoegsel"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Achternaam"
						maxlength="255"
						:value.sync="klant.achternaam"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Telefoonnummer"
						maxlength="255"
						:value.sync="klant.telefoonnummer"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Email adres"
						maxlength="255"
						:value.sync="klant.emailadres"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Functie"
						maxlength="255"
						:value.sync="klant.functie"
						:loading="klantLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="klantLoading"
						label="Aanmaak kanaal"
						maxlength="255"
						:value.sync="klant.aanmaakkanaal"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Bron organisatie"
						maxlength="255"
						:value.sync="klant.bronorganisatie"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Bedrijfsnaam"
						maxlength="255"
						:value.sync="klant.bedrijfsnaam"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Website Url"
						maxlength="255"
						:value.sync="klant.websiteUrl"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Url"
						maxlength="255"
						:value.sync="klant.url"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Geverifieerd"
						maxlength="255"
						:value.sync="klant.geverifieerd"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Subject"
						maxlength="255"
						:value.sync="klant.subject"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Subject Identificatie"
						maxlength="255"
						:value.sync="klant.subjectIdentificatie"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Subject Type"
						maxlength="255"
						:value.sync="klant.subjectType"
						:loading="klantLoading" />
				</div>

				<div v-if="succesMessage" class="success">
					Klant succesvol opgeslagen
				</div>
			</div>

			<NcButton type="primary" @click="addKlant">
				Starten
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcTextField,
} from '@nextcloud/vue'

export default {
	name: 'AddKlant',
	components: {
		NcModal,
		NcTextField,
		NcButton,
	},
	data() {
		return {
			klant: {
				voornaam: '',
				tussenvoegsel: '',
				achternaam: '',
				telefoonnummer: '',
				emailadres: '',
				adres: '',
				aanmaakkanaal: '',
				functie: '',
				bronorganisatie: '',
				bedrijfsnaam: '',
				websiteUrl: '',
				url: '',
				geverifieerd: '',
				subject: '',
				subjectIdentificatie: '',
				subjectType: '',
			},
			succesMessage: false,
			catalogiLoading: false,
			metaDataLoading: false,
			klantLoading: false,
			hasUpdated: false,
		}
	},
	updated() {
		if (store.modal === 'addKlant' && !this.hasUpdated) {
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
		addKlant() {
			this.klantLoading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/klanten',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(this.klant),
				},
			)
				.then((response) => {
					this.succesMessage = true
					this.publicationLoading = false
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					this.publicationLoading = false
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

.zaakDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}
</style>
