<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editTaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modalContent">
			<h2>Taak aanpassen</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div v-if="!succes" class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="taak.title"
					label="Titel"
					maxlength="255" />

				<NcSelect
					v-bind="zaak"
					v-model="zaak.value"
					:disabled="loading"
					input-label="Zaak"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="taak.type"
					label="Type"
					maxlength="255" />

				<NcSelect
					v-bind="statusOptions"
					v-model="statusOptions.value"
					:disabled="loading"
					input-label="Status"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="taak.onderwerp"
					label="Onderwerp"
					maxlength="255" />

				<NcTextArea
					:disabled="loading"
					:value.sync="taak.toelichting"
					label="Toelichting" />

				<NcTextField
					:disabled="loading"
					:value.sync="taak.actie"
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
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
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
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'EditTaak',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
		NcLoadingIcon,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (store.modal === 'editTaak' && this.hasUpdated) {
			if (this.taak === store.taakItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editTaak' && !this.hasUpdated) {
			this.taak = store.taakItem
			this.fetchZaken()
			this.setStatusOptions()
			this.hasUpdated = true
		}
	},
	methods: {
		editTaak() {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/taken/${this.taak.id}`,
				{
					method: 'PUT',
					body: JSON.stringify(store.taakItem),
				},
			)
				.then((response) => {
					this.succes = true
					this.loading = false
					store.getTakenList()
					response.json().then((data) => {
						store.setTaakItem(data)
					})
					// Get the modal to self close
					const self = this
					setTimeout(function() {
						self.succes = false
						store.setModal(false)
					}, 2000)
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
