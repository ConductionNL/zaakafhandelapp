<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editTaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Taak aanpassen</h2>

			<div v-if="!taakLoading">
				<div class="form-group">
					<NcTextField :disabled="taakLoading"
						label="Titel"
						maxlength="255"
						:value.sync="taak.title"
						:loading="taakLoading" />

					<NcTextField :disabled="taakLoading"
						label="Zaak"
						maxlength="255"
						:value.sync="taak.zaak"
						:loading="taakLoading" />

					<NcTextField :disabled="taakLoading"
						label="Type"
						maxlength="255"
						:value.sync="taak.type"
						:loading="taakLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="taak.status"
						:disabled="taakLoading"
						input-label="Status"
						:loading="catalogiLoading"
						required />

					<NcTextField :disabled="taakLoading"
						label="Onderwerp"
						maxlength="255"
						:value.sync="taak.onderwerp"
						:loading="taakLoading" />

					<NcTextArea :disabled="taakLoading"
						label="Toelichting"
						:value.sync="taak.toelichting"
						:loading="taakLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="loading"
						label="Actie"
						maxlength="255"
						:value.sync="taak.actie"
						:loading="taakLoading" />
				</div>

				<div v-if="succesMessage" class="success">
					Taak succesvol opgeslagen
				</div>
			</div>

			<NcLoadingIcon v-if="taakLoading"
				:size="100"
				appearance="dark"
				name="Edit taak model is aan het laden." />

			<NcButton type="primary" @click="editTaak">
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
	name: 'EditTaak',
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
			taak: {
				title: '',
				zaak: '',
				type: '',
				status: '',
				onderwerp: '',
				toelichting: '',
				actie: '',
				id: '',
			},
			loading: false,
			succesMessage: false,
			hasUpdated: false,
			taakLoading: false,
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
		if (store.modal === 'editTaak' && this.hasUpdated) {
			if (this.taak === store.taakItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editTaak' && !this.hasUpdated) {
			this.fetchCatalogi()
			this.fetchMetaData()
			this.fetchData(store.taakId)
			this.hasUpdated = true
			this.taak = store.taakItem
		}
	},
	methods: {
		fetchData(id) {
			this.taakLoading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/taken/${id}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.taak = data
						this.taak.data = JSON.stringify(data.data)
						this.catalogi.value = [data.catalogi]
						this.metaData.value = [data.metaData]
					})
					this.taakLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.taakLoading = false
				})
		},
		closeModal() {
			store.modal = false
		},
		editTaak() {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/taken/${this.taak.id}`,
				{
					method: 'PUT',
					body: JSON.stringify({
						title: this.taak.title,
						zaak: this.taak.zaak,
						type: this.taak.type,
						status: this.taak.status,
						onderwerp: this.taak.onderwerp,
						toelichting: this.taak.toelichting,
						actie: this.taak.actie,
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
