<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef" label-id="zaaktypeForm" @close="closeModal">
		<div class="modalContent">
			<h2>{{ zaaktype?.id ? t('zaakafhandelapp', 'Case type {action}', { action: t('zaakafhandelapp', 'edit') }) : t('zaakafhandelapp', 'Case type {action}', { action: t('zaakafhandelapp', 'create') }) }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>{{ zaaktype.id ? t('zaakafhandelapp', 'Case type successfully {action}', { action: t('zaakafhandelapp', 'updated') }) : t('zaakafhandelapp', 'Case type successfully {action}', { action: t('zaakafhandelapp', 'created') }) }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcTextField v-model="zaaktype.identificatie"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Identification')"
					maxlength="255"
					required />

				<NcTextField v-model="zaaktype.omschrijving"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Description')"
					maxlength="255" />

				<NcTextField v-model="zaaktype.omschrijvingGeneriek"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Generic description')"
					maxlength="9"
					required />

				<NcTextField v-model="zaaktype.vertrouwelijkheidaanduiding"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Confidentiality designation')"
					maxlength="9"
					required />

				<NcTextField v-model="zaaktype.doel"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Goal')"
					maxlength="9"
					required />

				<NcTextField v-model="zaaktype.aanleiding"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Cause')"
					required />

				<NcTextField v-model="zaaktype.toelichting"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Explanation')"
					maxlength="255" />

				<NcTextField v-model="zaaktype.indicatieInternOfExtern"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Internal or external indication')" />

				<NcTextField v-model="zaaktype.handelingInitiator" :disabled="loading" :label="t('zaakafhandelapp', 'Action initiator')" />

				<NcTextField v-model="zaaktype.onderwerp" :disabled="loading" :label="t('zaakafhandelapp', 'Subject')" />

				<NcTextField v-model="zaaktype.handelingBehandelaar"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Action handler')" />

				<NcTextField v-model="zaaktype.doorlooptijd" :disabled="loading" :label="t('zaakafhandelapp', 'Lead time')" />

				<NcTextField v-model="zaaktype.servicenorm" :disabled="loading" :label="t('zaakafhandelapp', 'Service standard')" />

				<NcTextField v-model="zaaktype.opschortingEnAanhoudingMogelijk"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Suspension and stay possible')" />

				<NcTextField v-model="zaaktype.verlengingMogelijk" :disabled="loading" :label="t('zaakafhandelapp', 'Extension possible')" />

				<NcTextField v-model="zaaktype.verlengingstermijn" :disabled="loading" :label="t('zaakafhandelapp', 'Extension term')" />

				<NcTextField v-model="zaaktype.publicatieIndicatie"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Publication indication')" />

				<NcTextField v-model="zaaktype.publicatietekst" :disabled="loading" :label="t('zaakafhandelapp', 'Publication text')" />

				<NcTextField v-model="zaaktype.productenOfDiensten"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Products or services')" />

				<NcTextField v-model="zaaktype.selectielijstProcestype"
					:disabled="loading"
					:label="t('zaakafhandelapp', 'Selection list process type')" />

				<NcTextField v-model="zaaktype.referentieproces" :disabled="loading" :label="t('zaakafhandelapp', 'Reference process name')" />

				<NcTextField v-model="zaaktype.catalogus" :disabled="loading" :label="t('zaakafhandelapp', 'Catalogue')" />

				<NcTextField v-model="zaaktype.beginGeldigheid" :disabled="loading" :label="t('zaakafhandelapp', 'Start of validity')" />

				<NcTextField v-model="zaaktype.eindeGeldigheid" :disabled="loading" :label="t('zaakafhandelapp', 'End of validity')" />

				<NcTextField v-model="zaaktype.beginObject" :disabled="loading" :label="t('zaakafhandelapp', 'Start of object')" />

				<NcTextField v-model="zaaktype.eindeObject" :disabled="loading" :label="t('zaakafhandelapp', 'End of object')" />

				<NcTextField v-model="zaaktype.versiedatum" :disabled="loading" :label="t('zaakafhandelapp', 'Version date')" />
			</div>

			<NcButton v-if="success === null"
				:disabled="!zaaktype.identificatie
					|| !zaaktype.omschrijvingGeneriek
					|| !zaaktype.vertrouwelijkheidaanduiding
					|| !zaaktype.doel
					|| !zaaktype.aanleiding
					|| loading"
				variant="primary"
				@click="saveZaakType()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				{{ zaaktype?.id ? t('zaakafhandelapp', 'Save') : t('zaakafhandelapp', 'Create') }}
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
	/**
	 * @spec openspec/specs/ui-modals/spec.md#REQ-004
	 */
	mounted() {
		if (zaakTypeStore.zaakTypeItem?.id) {
			this.initZaaktype()
		}
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-004
		 */
		initZaaktype() {
			this.zaaktype = {
				...this.zaaktype,
				...zaakTypeStore.zaakTypeItem,
			}

			const selectedArchiefStatus = this.archiefstatus.options.find((options) => options.id === this.zaaktype.archiefstatus)
			this.archiefstatus.value = selectedArchiefStatus || null
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModal() {
			navigationStore.setModal(null)
			clearTimeout(this.closeModalTimeout)
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-003
		 */
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
