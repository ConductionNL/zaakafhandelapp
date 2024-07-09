<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editZaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modalContent">
			<h2>Zaak aanpassen</h2>
			<div class="form-group">
				<NcTextField :disabled="zaakLoading"
					label="Identificatie"
					maxlength="255"
					:value.sync="zaak.identificatie"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Omschrijving"
					maxlength="255"
					:value.sync="zaak.omschrijving" />
				<NcTextField :disabled="zaakLoading"
					label="Bronorganisatie"
					maxlength="9"
					:value.sync="zaak.bronorganisatie"
					required />
				<NcTextField :disabled="zaakLoading"
					label="VerantwoordelijkeOrganisatie"
					maxlength="9"
					:value.sync="zaak.verantwoordelijkeOrganisatie"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Startdatum"
					maxlength="9"
					:value.sync="zaak.startdatum"
					required />
				<NcSelect v-bind="zaaktype"
					v-model="zaak.zaaktype"
					input-label="Zaaktype"
					:loading="zaakTypeLoading"
					:disabled="zaakLoading || zaakTypeLoading"
					required />
				<NcSelect v-bind="archiefstatus"
					v-model="zaak.archiefstatus"
					input-label="Archiefstatus"
					:disabled="zaakLoading"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Registratiedatum"
					maxlength="255"
					:value.sync="zaak.registratiedatum" />
				<NcTextArea :disabled="zaakLoading"
					label="Toelichting"
					:value.sync="zaak.toelichting" />
			</div>
			<div v-if="succesMessage" class="success">
				Zaak succesvol opgeslagen
			</div>
			<NcButton :disabled="!zaak.identificatie || !zaakTypeLoading || !zaak.bronorganisatie || !zaak.verantwoordelijkeOrganisatie || !zaak.startdatum" type="primary" @click="editZaak">
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcSelect, NcTextArea } from '@nextcloud/vue'

export default {
	name: 'EditZaak',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
		NcTextArea,
	},
	data() {
		return {
			zaak: {
				identificatie: '',
				omschrijving: '',
				zaaktype: {},
				registratiedatum: '',
				toelichting: '',
				bronorganisatie: '',
				verantwoordelijkeOrganisatie: '',
				startdatum: '',
				archiefstatus: {},
			},
			zaaktype: {},
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
		if (store.modal === 'editZaak' && this.hasUpdated) {
			if (this.zaak === store.zaakItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editZaak' && !this.hasUpdated) {
			this.fetchZaakType()
			this.hasUpdated = true
			this.zaak = store.zaakItem
		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
		editZaak() {
			this.zaakLoading = true
			fetch(
				`index.php/apps/zaakafhandelapp/api/zrc/zaken/${this.zaak.uuid}`,
				{
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						identificatie: this.identificatie,
						omschrijving: this.omschrijving,
						bronorganisatie: this.bronorganisatie,
						verantwoordelijkeOrganisatie: this.verantwoordelijkeOrganisatie,
						startdatum: this.startdatum,
						zaaktype: this.zaaktype.value,
						archiefstatus: this.archiefstatus.value,
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
