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
				<NcTextField :disabled="loading"
					:value.sync="zaaktype.identificatie"
					:label="t('zaakafhandelapp', 'Identification')"
					maxlength="255"
					required />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.omschrijving"
					:label="t('zaakafhandelapp', 'Description')"
					maxlength="255" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.omschrijvingGeneriek"
					:label="t('zaakafhandelapp', 'Generic description')"
					maxlength="9"
					required />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.vertrouwelijkheidaanduiding"
					:label="t('zaakafhandelapp', 'Confidentiality designation')"
					maxlength="9"
					required />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.doel"
					:label="t('zaakafhandelapp', 'Goal')"
					maxlength="9"
					required />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.aanleiding"
					:label="t('zaakafhandelapp', 'Cause')"
					required />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.toelichting"
					:label="t('zaakafhandelapp', 'Explanation')"
					maxlength="255" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.indicatieInternOfExtern"
					:label="t('zaakafhandelapp', 'Internal or external indication')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.handelingInitiator" :label="t('zaakafhandelapp', 'Action initiator')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.onderwerp" :label="t('zaakafhandelapp', 'Subject')" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.handelingBehandelaar"
					:label="t('zaakafhandelapp', 'Action handler')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.doorlooptijd" :label="t('zaakafhandelapp', 'Lead time')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.servicenorm" :label="t('zaakafhandelapp', 'Service standard')" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.opschortingEnAanhoudingMogelijk"
					:label="t('zaakafhandelapp', 'Suspension and stay possible')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.verlengingMogelijk" :label="t('zaakafhandelapp', 'Extension possible')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.verlengingstermijn" :label="t('zaakafhandelapp', 'Extension term')" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.publicatieIndicatie"
					:label="t('zaakafhandelapp', 'Publication indication')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.publicatietekst" :label="t('zaakafhandelapp', 'Publication text')" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.productenOfDiensten"
					:label="t('zaakafhandelapp', 'Products or services')" />

				<NcTextField :disabled="loading"
					:value.sync="zaaktype.selectielijstProcestype"
					:label="t('zaakafhandelapp', 'Selection list process type')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.referentieproces" :label="t('zaakafhandelapp', 'Reference process name')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.catalogus" :label="t('zaakafhandelapp', 'Catalogue')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.beginGeldigheid" :label="t('zaakafhandelapp', 'Start of validity')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.eindeGeldigheid" :label="t('zaakafhandelapp', 'End of validity')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.beginObject" :label="t('zaakafhandelapp', 'Start of object')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.eindeObject" :label="t('zaakafhandelapp', 'End of object')" />

				<NcTextField :disabled="loading" :value.sync="zaaktype.versiedatum" :label="t('zaakafhandelapp', 'Version date')" />
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
