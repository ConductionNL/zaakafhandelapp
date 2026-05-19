<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakStore, zaakTypeStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="zaakForm"
		@close="closeModal">
		<div class="modalContent">
			<h2>{{ zaakStore.zaakItem?.id ? t('zaakafhandelapp', 'Case {action}', { action: t('zaakafhandelapp', 'edit') }) : t('zaakafhandelapp', 'Case {action}', { action: t('zaakafhandelapp', 'create') }) }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>{{ zaak.id ? t('zaakafhandelapp', 'Case successfully {action}', { action: t('zaakafhandelapp', 'updated') }) : t('zaakafhandelapp', 'Case successfully {action}', { action: t('zaakafhandelapp', 'created') }) }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="!success" type="error">
					<p>{{ zaak.id ? t('zaakafhandelapp', 'Case not successfully {action}', { action: t('zaakafhandelapp', 'updated') }) : t('zaakafhandelapp', 'Case not successfully {action}', { action: t('zaakafhandelapp', 'created') }) }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcTextField :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Identification')"
					maxlength="255"
					:value.sync="zaak.identificatie"
					required />
				<NcTextField :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Description')"
					maxlength="255"
					:value.sync="zaak.omschrijving" />
				<NcTextField :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Source organisation')"
					maxlength="9"
					:value.sync="zaak.bronorganisatie"
					required />
				<NcTextField :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Responsible organisation')"
					maxlength="9"
					:value.sync="zaak.verantwoordelijkeOrganisatie"
					required />
				<NcTextField :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Start date')"
					maxlength="9"
					:value.sync="zaak.startdatum"
					required />
				<NcSelect v-bind="zaakType"
					v-model="zaakType.value"
					:input-label="t('zaakafhandelapp', 'Case type')"
					:loading="zaakTypeLoading"
					:disabled="true ||zaakLoading || zaakTypeLoading"
					required />
				<NcSelect v-bind="archiefstatus"
					v-model="archiefstatus.value"
					:input-label="t('zaakafhandelapp', 'Archive status')"
					:disabled="zaakLoading"
					required />
				<NcSelect
					v-bind="klanten"
					v-model="klanten.value"
					:input-label="t('zaakafhandelapp', 'Customer')"
					:loading="klantenLoading"
					:disabled="zaakLoading" />
				<NcTextField :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Registration date')"
					maxlength="255"
					:value.sync="zaak.registratiedatum" />
				<NcTextArea :disabled="zaakLoading"
					:label="t('zaakafhandelapp', 'Explanation')"
					:value.sync="zaak.toelichting" />
			</div>

			<NcButton v-if="success === null"
				:disabled="loading || !zaak.identificatie || zaakTypeLoading || !zaak.bronorganisatie || !zaak.verantwoordelijkeOrganisatie || !zaak.startdatum"
				type="primary"
				@click="saveZaak()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-else-if="!loading && zaak.id" :size="20" />
					<Plus v-else-if="!loading && !zaak.id" :size="20" />
				</template>
				{{ zaak.id ? t('zaakafhandelapp', 'Save') : t('zaakafhandelapp', 'Create') }}
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
	NcNoteCard,
	NcButton,
	NcTextField,
	NcSelect,
	NcTextArea,
	NcLoadingIcon,
} from '@nextcloud/vue'

// icons
import Plus from 'vue-material-design-icons/Plus.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

// entities
import { Zaak } from '../../entities/index.js'

export default {
	name: 'WidgetZaakForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
		NcTextArea,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			default: false,
			required: false,
		},
		selectedKlantFromWidget: {
			type: Object,
			default: null,
			required: false,
		},
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
				klant: '',
			},
			loading: false,
			success: null,
			error: null,
			zaakLoading: false,
			zaakType: {},
			klanten: [],
			klantenLoading: false,
			zaakTypeLoading: false,
			archiefstatus: {
				options: [
					{
						id: 'nog_te_archiveren',
						label: t('zaakafhandelapp', 'Yet to be archived'),
					},
					{
						id: 'gearchiveerd',
						label: t('zaakafhandelapp', 'Archived'),
					},
					{
						id: 'gearchiveerd_procestermijn_onbekend',
						label: t('zaakafhandelapp', 'Archived process term unknown'),
					},
					{
						id: 'overgedragen',
						label: t('zaakafhandelapp', 'Transferred'),
					},
				],
			},
		}
	},
	mounted() {
		this.setArchiefStatusOptions()
		// this.fetchZaakType()
		this.fetchKlanten()

		// If zaakItem in the store is set. apply it to zaak in this modal.
		if (zaakStore.zaakItem?.id) {
			this.zaak = {
				...this.zaak,
				...zaakStore.zaakItem,
			}
		}
	},
	methods: {
		closeModal() {
			this?.dashboardWidget && this.$emit('close')
			this.success = null
			this.loading = false
			this.zaak = {
				identificatie: '',
				omschrijving: '',
				zaaktype: {},
				registratiedatum: '',
				toelichting: '',
				bronorganisatie: '',
				verantwoordelijkeOrganisatie: '',
				startdatum: '',
				archiefstatus: '',
				klant: '',
			}
			if (this.dashboardWidget === true) {
				this.$emit('save-success')
			} else {
				navigationStore.setModal(null)
				this.$emit('close-modal')
			}
		},
		fetchKlanten() {
			this.klantenLoading = true

			klantStore.refreshKlantenList()
				.then(({ response, data }) => {
					let selectedKlant = data.filter((klant) => klant?.id.toString() === zaakStore.zaakItem?.klant?.toString())[0] || null

					if (this.selectedKlantFromWidget) {
						selectedKlant = data.filter((klant) => klant?.id.toString() === this.selectedKlantFromWidget.id?.toString())[0] || null
					}
					this.klanten = {
						options: data.map((klant) => ({
							id: klant.id,
							label: klant.type === 'persoon' ? `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}` : klant.bedrijfsnaam,
						})),
						value: selectedKlant
							? {
								id: selectedKlant?.id,
								label: selectedKlant?.type === 'persoon' ? `${selectedKlant.voornaam} ${selectedKlant.tussenvoegsel} ${selectedKlant.achternaam}` : selectedKlant.bedrijfsnaam,
							}
							: null,
					}

					this.klantenLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.klantenLoading = false
				})
		},
		fetchZaakType() {
			this.zaakTypeLoading = true

			zaakTypeStore.refreshZaakTypenList()
				.then(({ entities }) => {
					const selectedZaakType = entities.find((zaakType) => zaakType.id === this.zaak.zaaktype.id)

					this.zaakType = {
						options: entities.map((zaakType) => ({
							id: zaakType.id,
							label: zaakType.name,
						})),
						value: selectedZaakType
							? {
								id: selectedZaakType.id,
								label: selectedZaakType.name,
							  }
							: null,
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.zaakTypeLoading = false
				})
		},
		setArchiefStatusOptions() {
			const selectedArchiefStatusOption = this.archiefstatus.options.find((options) => options.id === this.zaak.archiefstatus)

			if (selectedArchiefStatusOption) {
				this.archiefstatus.value = {
					id: selectedArchiefStatusOption.id ?? '',
					label: selectedArchiefStatusOption.label ?? '',
				}
			}
		},
		saveZaak() {
			this.loading = true

			const newZaak = new Zaak({
				...this.zaak,
				archiefstatus: this.archiefstatus.value?.id || '',
				zaaktype: this.zaakType.value?.id || '',
				klant: this.klanten.value?.id || '',
			})

			zaakStore.saveZaak(newZaak)
				.then(({ response }) => {
					this.success = response.ok
					setTimeout(this.closeModal, 2500)

					this?.dashboardWidget && this.$emit('save-success')
				})
				.catch((err) => {
					console.error(err)
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
