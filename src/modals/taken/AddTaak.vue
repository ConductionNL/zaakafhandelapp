<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addTaak'" ref="modalRef" @close="closeModal">
		<div class="modalContent">
			<h2>Taak toevoegen</h2>

			<div class="formContainer">
				<div class="form-group">
					<NcTextField :disabled="taakLoading"
						label="Titel"
						maxlength="255"
						:value.sync="title"
						:loading="taakLoading" />

					<NcSelect v-bind="zaak"
						v-model="zaak.value"
						input-label="Zaak"
						:loading="zaakLoading"
						:disabled="taakLoading || zaakLoading || store.taakZaakId !== false "
						required />

					<NcTextField :disabled="taakLoading"
						label="Type"
						maxlength="255"
						:value.sync="type"
						:loading="taakLoading" />

					<NcSelect v-bind="statusOptions"
						v-model="status"
						:disabled="taakLoading"
						input-label="Status"
						required />

					<NcTextField :disabled="taakLoading"
						label="Onderwerp"
						maxlength="255"
						:value.sync="onderwerp"
						:loading="taakLoading" />

					<NcTextArea :disabled="taakLoading"
						label="Toelichting"
						:value.sync="toelichting"
						:loading="taakLoading" />
				</div>

				<div class="form-group">
					<NcTextField :disabled="taakLoading"
						label="Actie"
						maxlength="255"
						:value.sync="actie"
						:loading="taakLoading" />
				</div>

				<div v-if="succesMessage" class="success">
					Taak succesvol opgeslagen
				</div>
			</div>

			<NcButton :disabled="!title" type="primary" @click="addTaak">
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
	name: 'AddTaak',
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
			zaak: {},
			type: '',
			status: '',
			onderwerp: '',
			toelichting: '',
			actie: '',
			succesMessage: false,
			taakLoading: false,
			zaakLoading: false,
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
		if (store.modal === 'addTaak' && !this.hasUpdated) {
			this.fetchZaken()
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			this.hasUpdated = false
			store.setModal(false)
			store.setTaakZaakId(false)

		},
		addTaak() {
			this.taakLoading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/taken',
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
					this.taakLoading = false
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					this.taakLoading = false
					console.error(err)
				})
		},
		fetchZaken() {
			this.zaakLoading = true
			fetch('/index.php/apps/zaakafhandelapp/api/zrc/zaken', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {
						const selectedZaak = Object.entries(data.results).find((zaak) => zaak[1].uuid === store.taakZaakId)

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
