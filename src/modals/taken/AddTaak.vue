<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addTaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modalContent">
			<h2>Taak toevoegen</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="title"
					label="Titel"
					maxlength="255" />

				<NcSelect
					v-bind="zaak"
					v-model="zaak.value"
					:disabled="loading || zaakLoading || store.taakZaakId !== false "
					input-label="Zaak"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="type"
					label="Type"
					maxlength="255" />

				<NcSelect
					v-bind="statusOptions"
					v-model="status"
					:disabled="loading"
					input-label="Status"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="onderwerp"
					label="Onderwerp"
					maxlength="255" />

				<NcTextArea
					:disabled="loading"
					:value.sync="toelichting"
					label="Toelichting" />

				<NcTextField
					:disabled="loading"
					:value.sync="actie"
					label="Actie"
					maxlength="255" />
			</div>

			<NcButton
				v-if="!succes"
				:disabled="!store.attachmentItem.title || loading"
				type="primary"
				@click="addAttachment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Plus v-if="!loading" :size="20" />
				</template>
				Toevoegen
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
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'AddTaak',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
		NcLoadingIcon,
		// Icons
		Plus,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (store.modal === 'addTaak' && !this.hasUpdated) {
			this.fetchZaken()
			this.hasUpdated = true
		}
	},
	methods: {
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
