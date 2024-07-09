<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addDocument'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Document toevoegen</h2>

			<div class="formContainer">
				<div class="form-group">
					<NcTextField :disabled="documentLoading"
						label="Titel"
						maxlength="255"
						:value.sync="title"
						:loading="documentLoading" />

					<NcTextField :disabled="documentLoading"
						label="Zaak"
						maxlength="255"
						:value.sync="zaak"
						:loading="documentLoading" />

					<NcTextField :disabled="documentLoading"
						label="Type"
						maxlength="255"
						:value.sync="type"
						:loading="documentLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="status"
						:disabled="documentLoading"
						input-label="Status"
						:loading="catalogiLoading"
						required />

					<NcTextField :disabled="documentLoading"
						label="Onderwerp"
						maxlength="255"
						:value.sync="onderwerp"
						:loading="documentLoading" />

					<NcTextArea :disabled="documentLoading"
						label="Toelichting"
						:value.sync="toelichting"
						:loading="documentLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="documentLoading"
						label="Actie"
						maxlength="255"
						:value.sync="actie"
						:loading="documentLoading" />
				</div>

				<div v-if="succesMessage" class="success">
					Document succesvol opgeslagen
				</div>
			</div>

			<NcButton type="primary" @click="addDocument">
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
	name: 'AddDocument',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
	},
	data() {
		return {
			document: {
				title: '',
			},
			catalogi: {},
			metaData: {},
			succesMessage: false,
			documentLoading: false,
			hasUpdated: false,
		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
		addDocument() {
			this.documentLoading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/taken',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(this.document),
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
