<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editDocument'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Document aanpassen</h2>

			<div v-if="!documentLoading">
				<div class="form-group">
					<NcTextField :disabled="documentLoading"
						label="Titel"
						maxlength="255"
						:value.sync="document.title"
						:loading="documentLoading" />

					<NcTextField :disabled="documentLoading"
						label="Zaak"
						maxlength="255"
						:value.sync="document.zaak"
						:loading="documentLoading" />

					<NcTextField :disabled="documentLoading"
						label="Type"
						maxlength="255"
						:value.sync="document.type"
						:loading="documentLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="document.status"
						:disabled="documentLoading"
						input-label="Status"
						:loading="catalogiLoading"
						required />

					<NcTextField :disabled="documentLoading"
						label="Onderwerp"
						maxlength="255"
						:value.sync="document.onderwerp"
						:loading="documentLoading" />

					<NcTextArea :disabled="documentLoading"
						label="Toelichting"
						:value.sync="document.toelichting"
						:loading="documentLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="loading"
						label="Actie"
						maxlength="255"
						:value.sync="document.actie"
						:loading="documentLoading" />
				</div>

				<div v-if="succesMessage" class="success">
					Document succesvol opgeslagen
				</div>
			</div>

			<NcLoadingIcon v-if="documentLoading"
				:size="100"
				appearance="dark"
				name="Edit document model is aan het laden." />

			<NcButton type="primary" @click="editDocument">
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
	name: 'EditDocument',
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
			document: {
				title: '',
			},
			loading: false,
			succesMessage: false,
			hasUpdated: false,
			documentLoading: false,
		}
	},
	updated() {
		if (store.modal === 'editDocument' && this.hasUpdated) {
			if (this.document === store.documentItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editDocument' && !this.hasUpdated) {
			this.fetchData(store.documentId)
			this.hasUpdated = true
			this.document = store.documentItem
		}
	},
	methods: {
		fetchData(id) {
			this.documentLoading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/taken/${id}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.document = data
						this.document.data = JSON.stringify(data.data)
					})
					this.documentLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.documentLoading = false
				})
		},
		closeModal() {
			store.modal = false
		},
		editDocument() {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/taken/${this.document.id}`,
				{
					method: 'PUT',
					body: JSON.stringify(this.document),
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
