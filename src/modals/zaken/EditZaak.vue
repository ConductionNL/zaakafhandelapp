<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editZaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modalContent">
			<h2>Zaak aanpassen</h2>
			<div>store{{ store.zaakItem }}</div>
			<div>zaak{{ zaak }}</div>
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
					v-model="zaak.zaaktype.value"
					input-label="Zaaktype"
					:loading="zaakTypeLoading"
					:disabled="zaakLoading || zaakTypeLoading"
					required />
				<NcSelect v-bind="archiefstatus"
					v-model="zaak.archiefstatus.value"
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
			<NcButton :disabled="!identificatie || !zaaktype.value || !bronorganisatie || !verantwoordelijkeOrganisatie || !startdatum" type="primary" @click="editZaak">
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
				identificatie: 'test',
				omschrijving: '',
				zaaktype: {},
				registratiedatum: '',
				toelichting: '',
				bronorganisatie: '',
				verantwoordelijkeOrganisatie: '',
				startdatum: '',
				archiefstatus: {},
			},
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
			console.log('test1')
		}
		if (store.modal === 'editZaak' && !this.hasUpdated) {
			console.log('test')
			this.hasUpdated = true
			this.zaak = store.zaakItem
		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
		editZaak() {
			this.succesMessage = true
		},
	},
}
</script>
