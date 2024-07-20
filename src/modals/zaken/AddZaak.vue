<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addZaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modalContent">
			<h2>Zaak starten</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="identificatie"
					label="Identificatie"
					maxlength="255"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="omschrijving"
					label="Omschrijving"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="bronorganisatie"
					label="Bronorganisatie"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="verantwoordelijkeOrganisatie"
					label="VerantwoordelijkeOrganisatie"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="startdatum"
					label="Startdatum"
					maxlength="9"
					required />

				<NcSelect
					v-bind="zaaktype"
					v-model="zaaktype.value"
					:disabled="loading || zaakTypeLoading"
					input-label="Zaaktype"
					required />

				<NcSelect
					v-bind="archiefstatus"
					v-model="archiefstatus.value"
					input-label="Archiefstatus"
					:disabled="loading"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="registratiedatum"
					label="Registratiedatum"
					maxlength="255" />

				<NcTextArea
					:disabled="zaaloadingkLoading"
					:value.sync="toelichting"
					label="Toelichting" />
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
import { NcButton, NcModal, NcTextField, NcSelect, NcTextArea } from '@nextcloud/vue'

export default {
	name: 'AddZaak',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
		NcTextArea,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (store.modal === 'addZaak' && !this.hasUpdated) {
			this.fetchZaakType()
			this.hasUpdated = true
		}
	},
	methods: {
		addZaak() {
			this.zaakLoading = true
			fetch(
				'index.php/apps/zaakafhandelapp/api/zrc/zaken',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						identificatie: this.identificatie,
						omschrijving: this.omschrijving,
						bronorganisatie: this.bronorganisatie,
						verantwoordelijkeOrganisatie: this.verantwoordelijkeOrganisatie,
						startdatum: this.startdatum,
						zaaktype: this.zaaktype.value.id,
						archiefstatus: this.archiefstatus.value.id,
						registratiedatum: this.registratiedatum,
						toelichting: this.toelichting,
					}),
				},
			)
				.then((response) => {
					this.succesMessage = true
					this.zaakLoading = false
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					this.zaakLoading = false
					console.error(err)
				})
		},
		fetchZaakType() {
			this.zaakTypeLoading = true
			fetch('/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {

						this.zaaktype = {
							options: Object.entries(data.results).map((zaaktype) => ({
								id: zaaktype[1].id,
								label: zaaktype[1].name,
							})),

						}
					})
					this.zaakTypeLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.zaakTypeLoading = false
				})
		},
	},
}
</script>

<style>
.modalContent {
    margin: var(--zaa-margin-50);
    text-align: center;
}

</style>
