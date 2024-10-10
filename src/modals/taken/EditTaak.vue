<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editTaak'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Taak aanpassen</h2>
			<div v-if="!taakLoading">
				<div class="form-group">
					<NcTextField :disabled="taakLoading"
						label="Titel"
						maxlength="255"
						:value.sync="taak.title"
						:loading="taakLoading" />

					<NcSelect v-bind="zaak"
						v-model="zaak.value"
						input-label="Zaak"
						:loading="zaakLoading"
						:disabled="taakLoading || zaakLoading"
						required />

					<NcTextField :disabled="taakLoading"
						label="Type"
						maxlength="255"
						:value.sync="taak.type"
						:loading="taakLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="statusOptions.value"
						:disabled="taakLoading"
						input-label="Status"
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

			<NcButton :disabled="taakLoading" type="primary" @click="editTaak">
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
			zaak: '',
			loading: false,
			succesMessage: false,
			hasUpdated: false,
			taakLoading: false,
			zaakLoading: false,
			statusOptions: {
			},
		}
	},
	updated() {
		if (navigationStore.modal === 'editTaak' && this.hasUpdated) {
			if (this.taak === store.taakItem) return
			this.hasUpdated = false
		}
		if (navigationStore.modal === 'editTaak' && !this.hasUpdated) {
			this.taak = store.taakItem
			this.fetchZaken()
			this.setStatusOptions()
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			navigationStore.modal = false
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
		setStatusOptions() {
			const statusOptions = [
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
			]

			const selectedStatusOption = statusOptions.find((options) => options.id === this.taak.status)

			this.statusOptions = {
				options: statusOptions,
				value: {
					id: selectedStatusOption.id ?? '',
					label: selectedStatusOption.label ?? '',
				},
			}
		},
		fetchZaken() {
			this.zaakLoading = true
			fetch('/index.php/apps/zaakafhandelapp/api/zrc/zaken', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {
						const selectedZaak = Object.entries(data.results).find((zaak) => zaak[1].uuid === this.taak.zaak)

						this.zaak = {
							options: Object.entries(data.results).map((zaak) => ({
								id: zaak[1].uuid,
								label: zaak[1].identificatie,
							})),
							value: {
								id: selectedZaak[1].uuid ?? '',
								label: selectedZaak[1].identificatie ?? '',
							},

						}
					})
					this.zaakLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.zaakLoading = false
				})
		},
	},
}
</script>
