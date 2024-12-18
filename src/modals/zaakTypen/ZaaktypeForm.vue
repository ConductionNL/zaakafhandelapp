<script setup>
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef" label-id="zaaktypeForm" @close="closeModal">
		<div class="modalContent">
			<h2>Zaaktype {{ zaaktype?.id ? 'aanpassen' : 'aanmaken' }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Zaaktype succesvol {{ zaaktype.id ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.identificatie"
					label="Identificatie"
					maxlength="255"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.omschrijving"
					label="Omschrijving"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.omschrijvingGeneriek"
					label="omschrijvingGeneriek"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.vertrouwelijkheidaanduiding"
					label="vertrouwelijkheidaanduiding"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.doel"
					label="doel"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.aanleiding"
					label="aanleiding"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.toelichting"
					label="toelichting"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.indicatieInternOfExtern"
					label="indicatieInternOfExtern" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.handelingInitiator"
					label="handelingInitiator" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.onderwerp"
					label="onderwerp" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.handelingBehandelaar"
					label="handelingBehandelaar" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.doorlooptijd"
					label="doorlooptijd" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.servicenorm"
					label="servicenorm" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.opschortingEnAanhoudingMogelijk"
					label="opschortingEnAanhoudingMogelijk" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.verlengingMogelijk"
					label="verlengingMogelijk" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.verlengingstermijn"
					label="trefwoorden" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.publicatieIndicatie"
					label="publicatieIndicatie" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.publicatietekst"
					label="publicatietekst" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.productenOfDiensten"
					label="productenOfDiensten" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.selectielijstProcestype"
					label="selectielijstProcestype" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.referentieproces"
					label="Referentieprocesnaam" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.catalogus"
					label="catalogus" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.beginGeldigheid"
					label="beginGeldigheid" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.eindeGeldigheid"
					label="eindeGeldigheid" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.beginObject"
					label="beginObject" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.eindeObject"
					label="eindeObject" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaaktype.versiedatum"
					label="versiedatum" />
			</div>

			<NcButton v-if="success === null"
				:disabled="!zaaktype.identificatie
					|| !zaaktype.omschrijvingGeneriek
					|| !zaaktype.vertrouwelijkheidaanduiding
					|| !zaaktype.doel
					|| !zaaktype.aanleiding
					|| loading"
				type="primary"
				@click="saveZaakType()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				{{ zaaktype?.id ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcNoteCard, NcLoadingIcon } from '@nextcloud/vue'
import { ZaakType } from '../../entities/index.js'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'ZaaktypeForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
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
			archiefstatus: {
				options: [
					{ id: 'nog_te_archiveren', label: 'Nog te archiveren' },
					{ id: 'gearchiveerd', label: 'Gearchiveerd' },
					{ id: 'gearchiveerd_procestermijn_onbekend', label: 'Gearchiveerd procestermijn onbekend' },
					{ id: 'overgedragen', label: 'Overgedragen' },
				],
				value: null,
			},
			success: null,
			loading: false,
			error: false,
			closeModalTimeout: null,
		}
	},
	mounted() {
		if (zaakTypeStore.zaakTypeItem?.id) {
			this.initZaaktype()
		}
	},
	methods: {
		initZaaktype() {
			this.zaaktype = {
				...this.zaaktype,
				...zaakTypeStore.zaakTypeItem,
			}

			const selectedArchiefStatus = this.archiefstatus.options.find((options) => options.id === this.zaaktype.archiefstatus)
			this.archiefstatus.value = selectedArchiefStatus || null
		},
		closeModal() {
			navigationStore.setModal(null)
			clearTimeout(this.closeModalTimeout)
		},
		saveZaakType() {
			this.loading = true

			const zaakTypeItem = new ZaakType({
				...this.zaaktype,
				archiefstatus: this.archiefstatus.value?.id || null,
			})

			zaakTypeStore.saveZaakType(zaakTypeItem)
				.then(({ response }) => {
					this.success = response.ok
					this.closeModalTimeout = setTimeout(this.closeModal, 3000)
				})
				.catch((e) => {
					this.error = e
					this.success = false
				})
				.finally(() => {
					this.loading = false
				})
		},
	},
}
</script>
