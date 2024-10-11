<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'addZaak'" ref="modalRef" @close="navigationStore.setModal(false)">
		<div class="modalContent">
			<h2>Zaak starten</h2>
			<div class="form-group">
				<NcTextField :disabled="zaakLoading"
					label="Identificatie"
					maxlength="255"
					:value.sync="identificatie"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Omschrijving"
					maxlength="255"
					:value.sync="omschrijving" />
				<NcTextField :disabled="zaakLoading"
					label="Bronorganisatie"
					maxlength="9"
					:value.sync="bronorganisatie"
					required />
				<NcTextField :disabled="zaakLoading"
					label="VerantwoordelijkeOrganisatie"
					maxlength="9"
					:value.sync="verantwoordelijkeOrganisatie"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Startdatum"
					maxlength="9"
					:value.sync="startdatum"
					required />
				<NcSelect v-bind="zaaktype"
					v-model="zaaktype.value"
					input-label="Zaaktype"
					:loading="zaakTypeLoading"
					:disabled="zaakLoading || zaakTypeLoading"
					required />
				<NcSelect v-bind="archiefstatus"
					v-model="archiefstatus.value"
					input-label="Archiefstatus"
					:disabled="zaakLoading"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Registratiedatum"
					maxlength="255"
					:value.sync="registratiedatum" />
				<NcTextArea :disabled="zaakLoading"
					label="Toelichting"
					:value.sync="toelichting" />
			</div>
			<div v-if="succesMessage" class="success">
				Zaak succesvol gestart
			</div>

			<NcButton :disabled="!identificatie || !zaaktype.value || !bronorganisatie || !verantwoordelijkeOrganisatie || !startdatum" type="primary" @click="addZaak">
				Starten
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
			identificatie: '',
			omschrijving: '',
			zaaktype: {},
			registratiedatum: '',
			toelichting: '',
			bronorganisatie: '',
			verantwoordelijkeOrganisatie: '',
			startdatum: '',
			zaakLoading: false,
			succesMessage: false,
			hasUpdated: false,
			zaakTypeLoading: false,
			archiefstatus: {
				options: [
					{
						id: 'nog_te_archiveren',
						label: 'Nog te archiveren',
					},
					{
						id: 'gearchiveerd',
						label: 'Gearchiveerd',
					},
					{
						id: 'gearchiveerd_procestermijn_onbekend',
						label: 'Gearchiveerd procestermijn onbekend',
					},
					{
						id: 'overgedragen',
						label: 'Overgedragen',
					},
				],
			},
		}
	},
	updated() {
		if (navigationStore.modal === 'addZaak' && !this.hasUpdated) {
			this.fetchZaakType()
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			navigationStore.modal = false
		},
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
