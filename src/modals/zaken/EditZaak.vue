<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'editZaak'" ref="modalRef" @close="navigationStore.setModal(false)">
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
					v-model="zaaktype.value"
					input-label="Zaaktype"
					:loading="zaakTypeLoading"
					disabled
					required />
				<NcSelect v-bind="archiefstatus"
					v-model="archiefstatus.value"
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
				archiefstatus: '',
			},
			zaaktype: {},
			zaakLoading: false,
			succesMessage: false,
			hasUpdated: false,
			zaakTypeLoading: false,
			archiefstatus: {

			},
		}
	},
	updated() {
		if (navigationStore.modal === 'editZaak' && this.hasUpdated) {
			if (this.zaak === store.zaakItem) return
			this.hasUpdated = false
		}
		if (navigationStore.modal === 'editZaak' && !this.hasUpdated) {
			this.zaak = store.zaakItem
			this.setArchiefStatusOptions()
			this.fetchZaakType()
			this.hasUpdated = true
		}
	},
	methods: {
		closeModal() {
			navigationStore.modal = false
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
		setArchiefStatusOptions() {
			const archiefStatusOptions = [
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
			]

			const selectedArchiefStatusOption = archiefStatusOptions.find((options) => options.id === this.zaak.archiefstatus)

			this.archiefstatus = {
				options: archiefStatusOptions,
				value: {
					id: selectedArchiefStatusOption.id ?? '',
					label: selectedArchiefStatusOption.label ?? '',
				},
			}
		},
		fetchZaakType() {
			this.zaakTypeLoading = true
			fetch('/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen', {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {
						const selectedZaakType = Object.entries(data.results).find((zaaktype) => zaaktype[1].id === this.zaak.zaaktype)

						this.zaaktype = {
							options: Object.entries(data.results).map((zaaktype) => ({
								id: zaaktype[1].id,
								label: zaaktype[1].name,
							})),
							value: {
								id: selectedZaakType[1].id ?? '',
								label: selectedZaakType[1].name ?? '',
							},
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
