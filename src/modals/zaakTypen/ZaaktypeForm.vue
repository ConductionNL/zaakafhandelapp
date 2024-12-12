<script setup>
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef" label-id="zaaktypeForm" @close="closeModal">
		<div class="modalContent">
			<h2>Zaaktype {{ zaakTypeStore.zaakTypeItem.id ? 'aanpassen' : 'aanmaken' }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Zaaktype succesvol {{ zaakTypeStore.zaakTypeItem.id ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.identificatie"
					label="Identificatie"
					maxlength="255"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.omschrijving"
					label="Omschrijving"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.omschrijvingGeneriek"
					label="omschrijvingGeneriek"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.vertrouwelijkheidaanduiding"
					label="vertrouwelijkheidaanduiding"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.doel"
					label="doel"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.aanleiding"
					label="aanleiding"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.toelichting"
					label="toelichting"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.indicatieInternOfExtern"
					label="indicatieInternOfExtern" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.handelingInitiator"
					label="handelingInitiator" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.onderwerp"
					label="onderwerp" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.handelingBehandelaar"
					label="handelingBehandelaar" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.doorlooptijd"
					label="doorlooptijd" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.servicenorm"
					label="servicenorm" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.opschortingEnAanhoudingMogelijk"
					label="opschortingEnAanhoudingMogelijk" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.verlengingMogelijk"
					label="verlengingMogelijk" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.verlengingstermijn"
					label="trefwoorden" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.publicatieIndicatie"
					label="publicatieIndicatie" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.publicatietekst"
					label="publicatietekst" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.productenOfDiensten"
					label="productenOfDiensten" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.selectielijstProcestype"
					label="selectielijstProcestype" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.referentieproces"
					label="Referentieprocesnaam" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.catalogus"
					label="catalogus" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.beginGeldigheid"
					label="beginGeldigheid" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.eindeGeldigheid"
					label="eindeGeldigheid" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.beginObject"
					label="beginObject" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.eindeObject"
					label="eindeObject" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.versiedatum"
					label="versiedatum" />
			</div>

			<NcButton v-if="success === null"
				:disabled="!zaakTypeStore.zaakTypeItem.title || loading"
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
import { NcButton, NcModal, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'ZaaktypeForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcLoadingIcon,
		// Icons
		ContentSaveOutline,
	},
	data() {
		return {
			zaaktype: {
				identificatie: '',
				omschrijving: '',
				omschrijvingGeneriek: '',
				vertrouwelijkheidaanduiding: '',
				doel: '',
				aanleiding: '',
				toelichting: '',
				indicatieInternOfExtern: '',
				handelingInitiator: '',
				onderwerp: '',
				handelingBehandelaar: '',
				doorlooptijd: '',
				servicenorm: '',
				opschortingEnAanhoudingMogelijk: '',
				verlengingMogelijk: '',
				verlengingstermijn: '',
				publicatieIndicatie: '',
				publicatietekst: '',
				productenOfDiensten: '',
				selectielijstProcestype: '',
				referentieproces: '',
				catalogus: '',
				beginGeldigheid: '',
				eindeGeldigheid: '',
				beginObject: '',
				eindeObject: '',
				versiedatum: '',
			},
			success: null,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (navigationStore.modal === 'editZaakType' && this.hasUpdated) {
			if (this.zaakType === zaakTypeStore.zaakTypeItem) return
			this.hasUpdated = false
		}
		if (navigationStore.modal === 'editZaakType' && !this.hasUpdated) {
			this.zaakType = zaakTypeStore.zaakTypeItem
			this.hasUpdated = true
		}
	},
	methods: {
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
