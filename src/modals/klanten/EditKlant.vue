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
						label="Titel"
						maxlength="255"
						:value.sync="klant.title"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Zaak"
						maxlength="255"
						:value.sync="klant.zaak"
						:loading="klantLoading" />

					<NcTextField :disabled="klantLoading"
						label="Type"
						maxlength="255"
						:value.sync="klant.type"
						:loading="klantLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="klant.status"
						:disabled="klantLoading"
						input-label="Status"
						:loading="catalogiLoading"
						required />

					<NcTextField :disabled="klantLoading"
						label="Onderwerp"
						maxlength="255"
						:value.sync="klant.onderwerp"
						:loading="klantLoading" />

					<NcTextArea :disabled="klantLoading"
						label="Toelichting"
						:value.sync="klant.toelichting"
						:loading="klantLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="loading"
						label="Actie"
						maxlength="255"
						:value.sync="klant.actie"
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
	NcTextArea,
	NcSelect,
	NcLoadingIcon,
} from '@nextcloud/vue'

export default {
	name: 'EditKlant',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
		NcLoadingIcon,
	},
	data() {
		return {
			klant: {
				title: '',
				zaak: '',
				type: '',
				status: '',
				onderwerp: '',
				toelichting: '',
				actie: '',
				id: '',
			},
			catalogi: {
				value: [],
				options: [],
			},
			metaData: {
				value: [],
				options: [],
			},
			loading: false,
			succesMessage: false,
			catalogiLoading: false,
			metaDataLoading: false,
			hasUpdated: false,
			klantLoading: false,
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
		if (store.modal === 'editKlant' && this.hasUpdated) {
			if (this.klant === store.klantItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editKlant' && !this.hasUpdated) {
			this.fetchCatalogi()
			this.fetchMetaData()
			this.fetchData(store.klantId)
			this.hasUpdated = true
			this.klant = store.klantItem
		}
	},
	methods: {
		fetchData(id) {
			this.klantLoading = true
			fetch(
				`/index.php/apps/opencatalog/klanten/api/${id}`,
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
					thisklantLoading = false
				})
		},
		fetchCatalogi() {
			this.catalogiLoading = true
			fetch('/index.php/apps/opencatalog/catalogi/api', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {
						this.catalogi = {
							value: this.catalogi.value,
							inputLabel: 'Catalogi',
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
							inputLabel: 'MetaData',
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
		editKlant() {
			this.loading = true
			fetch(
				`/index.php/apps/opencatalog/taken/api/${this.klant.id}`,
				{
					method: 'PUT',
					body: JSON.stringify({
						title: this.klant.title,
						zaak: this.klant.zaak,
						type: this.klant.type,
						status: this.klant.status,
						onderwerp: this.klant.onderwerp,
						toelichting: this.klant.toelichting,
						actie: this.klant.actie,
					}),
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