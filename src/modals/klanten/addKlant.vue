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
						label="Titel"
						maxlength="255"
						:value.sync="title"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Zaak"
						maxlength="255"
						:value.sync="zaak"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Type"
						maxlength="255"
						:value.sync="type"
						:loading="klantLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="status"
						:disabled="klantLoading"
						input-label="Status"
						:loading="catalogiLoading"
						required />

					<NcTextField :disabled="klantLoading"
						label="Onderwerp"
						maxlength="255"
						:value.sync="onderwerp"
						:loading="klantLoading" />

					<NcTextArea :disabled="klantLoading"
						label="Toelichting"
						:value.sync="toelichting"
						:loading="klantLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="klantLoading"
						label="Actie"
						maxlength="255"
						:value.sync="actie"
						:loading="klantLoading" />
				</div>

				<div v-if="succesMessage" class="success">
					Klant succesvol opgeslagen
				</div>
			</div>

			<NcButton :disabled="!title" type="primary" @click="addKlant">
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
	NcTextArea,
	NcSelect,
} from '@nextcloud/vue'

export default {
	name: 'AddKlant',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
	},
	data() {
		return {
			title: '',
			zaak: '',
			type: '',
			status: '',
			onderwerp: '',
			toelichting: '',
			actie: '',
			catalogi: {},
			metaData: {},
			succesMessage: false,
			catalogiLoading: false,
			metaDataLoading: false,
			klantLoading: false,
			hasUpdated: false,
			statusOptions: {
				options: [
					{
						id: 'open',
						label: 'Open',
					},
					{
						id: 'ingediend',
						label: 'Ingediend',
					},
					{
						id: 'verwerkt',
						label: 'Verwerkt',
					},
					{
						id: 'gesloten',
						label: 'Gesloten',
					},
				],
			},
		}
	},
	updated() {
		if (store.modal === 'addKlant' && !this.hasUpdated) {
			this.fetchCatalogi()
			this.fetchMetaData()
			this.hasUpdated = true
		}
	},
	methods: {
		fetchCatalogi() {
			this.catalogiLoading = true
			fetch('/index.php/apps/opencatalog/catalogi/api', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {

						this.catalogi = {
							options: Object.entries(data.results).map((catalog) => ({
								id: catalog[1]._id,
								label: catalog[1].name,
							})),

						}
					})
					this.catalogiLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.catalogiLoading = false
				})
		},
		fetchMetaData() {
			this.metaDataLoading = true
			fetch('/index.php/apps/opencatalog/metadata/api', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {

						this.metaData = {
							options: Object.entries(data.results).map((metaData) => ({
								id: metaData[1]._id,
								label: metaData[1].name,
							})),

						}
					})
					this.metaDataLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.metaDataLoading = false
				})
		},
		closeModal() {
			store.modal = false
		},
		addKlant() {
			this.klantLoading = true
			fetch(
				'/index.php/apps/opencatalog/klanten/api',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						title: this.title,
						zaak: this.zaak,
						type: this.type,
						status: this.status,
						onderwerp: this.onderwerp,
						toelichting: this.toelichting,
						actie: this.actie,
					}),
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