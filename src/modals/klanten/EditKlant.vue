<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editKlant'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Klant aanpassen</h2>

			<div v-if="!klantLoading">
				<div class="form-group">
					<NcTextField :disabled="klantLoading"
						label="Voornaam"
						maxlength="255"
						:value.sync="klant.voornaam"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Tussenvoegsels"
						maxlength="255"
						:value.sync="klant.voorvoegsel"
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

			<NcLoadingIcon v-if="klantLoading"
				:size="100"
				appearance="dark"
				name="Edit klant model is aan het laden." />

			<NcButton type="primary" @click="editKlant">
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcTextField,
	NcLoadingIcon,
} from '@nextcloud/vue'

export default {
	name: 'EditKlant',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcLoadingIcon,
	},
	data() {
		return {
			klant: {
				voornaam: '',
				voorvoegsel: '',
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
				id: '',
			},
			loading: false,
			succesMessage: false,
			catalogiLoading: false,
			metaDataLoading: false,
			hasUpdated: false,
			klantLoading: false,
		}
	},
	updated() {
		if (store.modal === 'editKlant' && this.hasUpdated) {
			if (this.klant === store.klantItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editKlant' && !this.hasUpdated) {
			// uncomment when api works
			// this.fetchData(store.klantId)
			this.hasUpdated = true
			this.klant = store.klantItem
		}
	},
	methods: {
		fetchData(id) {
			this.klantLoading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/klanten/${id}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.klant = data
						this.klant.data = JSON.stringify(data.data)
						this.catalogi.value = [data.catalogi]
						this.metaData.value = [data.metaData]
					})
					this.klantLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.klantLoading = false
				})
		},
		closeModal() {
			store.modal = false
		},
		editKlant() {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/klanten/${this.klant.id}`,
				{
					method: 'PUT',
					body: JSON.stringify(this.klant),
				},
			)
				.then((response) => {
					this.succesMessage = true
					this.loading = false
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					this.loading = false
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
