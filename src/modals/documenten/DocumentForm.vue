<script setup>
import { navigationStore, documentStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="documentForm"
		@close="closeModal">
		<div class="modalContent">
			<h2>Document {{ IS_EDIT ? 'aanpassen' : 'aanmaken' }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Document succesvol {{ IS_EDIT ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null">
				<p>Selecteer een zaak om het document aan te maken.</p>
				<NcSelect v-bind="zaak"
					v-model="zaak.value"
					input-label="Zaak"
					:loading="zaakLoading"
					:disabled="zaakLoading" />

				<div class="form-group">
					<NcTextField label="Identificatie"
						maxlength="40"
						:value.sync="document.identificatie"
						required />

					<NcTextField label="Bronorganisatie"
						minlength="1"
						maxlength="9"
						:value.sync="document.bronorganisatie"
						required />

					<div>
						Creatiedatum
						<NcDateTimePicker v-model="document.creatiedatum"
							type="date"
							required
							confirm />
					</div>

					<NcTextField label="Titel"
						maxlength="200"
						:value.sync="document.titel"
						required />

					<NcSelect v-bind="vertrouwelijkheidaanduidingOptions"
						v-model="vertrouwelijkheidaanduidingOptions.value"
						input-label="Vertrouwelijkheid aanduiding" />

					<NcTextField label="Auteur"
						minlength="1"
						maxlength="200"
						:value.sync="document.auteur"
						required />

					<NcSelect v-bind="statusOptions"
						v-model="statusOptions.value"
						input-label="Status" />

					<NcCheckboxRadioSwitch :checked.sync="document.inhoudIsVervallen">
						Inhoud is vervallen
					</NcCheckboxRadioSwitch>

					<NcTextField label="formaat"
						maxlength="255"
						:value.sync="document.formaat" />

					<NcTextField label="taal"
						minlength="3"
						maxlength="3"
						:value.sync="document.taal"
						required />

					<NcTextField label="bestandsnaam"
						maxlength="255"
						:value.sync="document.bestandsnaam" />

					<NcTextField label="inhoud"
						maxlength="255"
						:value.sync="document.inhoud" />

					<NcInputField label="bestandsomvang"
						type="number"
						min="0"
						max="9223372036854776000"
						:value.sync="document.bestandsomvang" />

					<NcTextField label="link"
						maxlength="200"
						:value.sync="document.link" />

					<NcTextField label="beschrijving"
						maxlength="1000"
						:value.sync="document.beschrijving" />

					<div>
						Ontvangstdatum
						<NcDateTimePicker v-model="document.ontvangstdatum"
							type="date"
							confirm />
					</div>

					<div>
						Verzenddatum
						<NcDateTimePicker v-model="document.verzenddatum"
							type="date"
							confirm />
					</div>

					<NcCheckboxRadioSwitch :checked.sync="document.indicatieGebruiksrecht">
						Indicatie gebruiksrecht
					</NcCheckboxRadioSwitch>

					<NcTextField label="verschijningsvorm"
						:value.sync="document.verschijningsvorm" />

					<!--
                        TODO:
                        https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/redoc-1.5.0#tag/enkelvoudiginformatieobjecten/operation/enkelvoudiginformatieobject_create
                        ondertekening and integriteit zou hier nog moeten komen
                    -->

					<NcTextField label="informatieobject type"
						minlength="1"
						maxlength="200"
						required
						:value.sync="document.informatieobjecttype" />

					<!-- <NcTextField label="trefwoorden"
						:value.sync="document.trefwoorden" /> -->
				</div>
			</div>

			<NcButton v-if="success === null"
				:disabled="loading || !isValid()"
				type="primary"
				@click="saveDocument()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-else-if="!loading && IS_EDIT" :size="20" />
					<Plus v-else-if="!loading && !IS_EDIT" :size="20" />
				</template>
				{{ IS_EDIT ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
	NcInputField,
	NcNoteCard,
	NcButton,
	NcTextField,
	NcLoadingIcon,
	NcSelect,
	NcDateTimePicker,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'

// icons
import Plus from 'vue-material-design-icons/Plus.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

// entities
import { Document } from '../../entities/index.js'

export default {
	name: 'DocumentForm',
	components: {
		NcModal,
		NcInputField,
		NcTextField,
		NcButton,
		NcSelect,
		NcNoteCard,
		NcDateTimePicker,
		NcCheckboxRadioSwitch,
		NcLoadingIcon,
		Plus,
		ContentSaveOutline,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			default: false,
			required: false,
		},
	},
	data() {
		return {
			document: {
				identificatie: '',
				bronorganisatie: '',
				creatiedatum: new Date(),
				titel: '',
				vertrouwelijkheidaanduiding: 'openbaar',
				auteur: '',
				status: 'in_bewerking',
				inhoudIsVervallen: false,
				formaat: '',
				taal: '',
				bestandsnaam: '',
				inhoud: '',
				bestandsomvang: 0,
				link: '',
				beschrijving: '',
				ontvangstdatum: new Date(),
				verzenddatum: new Date(),
				indicatieGebruiksrecht: false,
				verschijningsvorm: '',
				informatieobjecttype: '',
				trefwoorden: '',
			},
			vertrouwelijkheidaanduidingOptions: {
				options: [
					{ label: 'Openbaar', value: 'openbaar' },
					{ label: 'Beperkt openbaar', value: 'beperkt_openbaar' },
					{ label: 'Intern', value: 'intern' },
					{ label: 'Zaakvertrouwelijk', value: 'zaakvertrouwelijk' },
					{ label: 'Vertrouwelijk', value: 'vertrouwelijk' },
					{ label: 'Confidentieel', value: 'confidentieel' },
					{ label: 'Geheim', value: 'geheim' },
					{ label: 'Zeer geheim', value: 'zeer_geheim' },
				],
				value: null,
			},
			statusOptions: {
				options: [
					{ label: 'In bewerking', value: 'in_bewerking' },
					{ label: 'Ter vaststelling', value: 'ter_vaststelling' },
					{ label: 'Definitief', value: 'definitief' },
					{ label: 'Gearchiveerd', value: 'gearchiveerd' },
				],
				value: null,
			},
			IS_EDIT: false,
			loading: false,
			success: null,
			error: null,
			zaakLoading: false,
			zaak: {
				options: [],
				value: null,
			},
		}
	},
	mounted() {
		if (documentStore.documentItem?.id) {
			this.IS_EDIT = true
			this.document = {
				...this.document,
				...documentStore.documentItem,
				creatiedatum: new Date(documentStore.documentItem.creatiedatum),
				ontvangstdatum: new Date(documentStore.documentItem.ontvangstdatum),
				verzenddatum: new Date(documentStore.documentItem.verzenddatum),
			}

			this.vertrouwelijkheidaanduidingOptions.value = this.vertrouwelijkheidaanduidingOptions.options.find((option) => option.value === documentStore.documentItem.vertrouwelijkheidaanduiding)
			this.statusOptions.value = this.statusOptions.options.find((option) => option.value === documentStore.documentItem.status)
		}

		this.fetchZaak()
	},
	methods: {
		closeModal() {
			navigationStore.setModal(null)
			documentStore.zaakId = null
			this.dashboardWidget && this.$emit('close-modal')
		},
		fetchZaak() {
			this.zaakLoading = true

			zaakStore.refreshZakenList()
				.then(({ entities }) => {
					const compareId = this.document?.zaak || documentStore.zaakId
					const selectedZaak = entities.find((zaak) => zaak.id === compareId)

					this.zaak = {
						options: entities.map((zaak) => ({
							label: zaak.identificatie,
							id: zaak.id,
						})),
						value: selectedZaak
							? {
								label: selectedZaak.identificatie,
								id: selectedZaak.id,
							}
							: null,
					}
				})
				.finally(() => {
					this.zaakLoading = false
				})
		},
		isValid() {
			return this.document.bronorganisatie
				&& this.document.creatiedatum
				&& this.document.titel
				&& this.document.auteur
				&& this.document.taal
				&& this.document.informatieobjecttype
		},
		saveDocument() {
			this.loading = true

			const newDocument = new Document({
				...this.document,
				zaak: this.zaak.value?.id,
				vertrouwelijkheidaanduiding: this.vertrouwelijkheidaanduidingOptions.value?.value ?? null,
				status: this.statusOptions.value?.value ?? null,
				creatiedatum: this.document.creatiedatum.toISOString(),
				ontvangstdatum: this.document.ontvangstdatum.toISOString(),
				verzenddatum: this.document.verzenddatum.toISOString(),
			})

			documentStore.saveDocument({
				...newDocument,
			})
				.then(({ response }) => {
					this.success = response.ok
					setTimeout(this.closeModal, 2500)

					this.dashboardWidget && this.$emit('save-success')
				})
				.catch((err) => {
					console.error(err)
					this.error = err.message || 'Er is iets fout gegaan bij het opslaan van het document.'
					this.success = false
				})
				.finally(() => {
					this.loading = false
				})
		},
	},
}
</script>

<style scoped>
.modalContent {
    margin: var(--zaa-margin-50, 12px);
    text-align: center;
}
</style>
