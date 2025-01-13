<script setup>
import { navigationStore, zaakStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="zaakForm"
		@close="closeModal">
		<div class="modalContent">
			<h2>Zaak {{ zaakStore.zaakItem?.id ? 'aanpassen' : 'aanmaken' }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Zaak succesvol {{ zaak.id ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="!success" type="error">
					<p>Zaak niet succesvol {{ zaak.id ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
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
				<NcSelect v-bind="zaakType"
					v-model="zaakType.value"
					input-label="Zaaktype"
					:loading="zaakTypeLoading"
					:disabled="zaakLoading || zaakTypeLoading"
					required />
				<NcSelect v-bind="archiefstatus"
					v-model="archiefstatus.value"
					input-label="Archiefstatus"
					:disabled="zaakLoading"
					required />
				<div>
					<p>Uiterlijke Einddatum Afdoening</p>
					<NcDateTimePicker
						v-model="zaak.uiterlijkeEinddatumAfdoening"
						:disabled="zaakLoading"
						required />
				</div>
				<NcTextField :disabled="zaakLoading"
					label="Registratiedatum"
					maxlength="255"
					:value.sync="zaak.registratiedatum" />
				<NcTextArea :disabled="zaakLoading"
					label="Toelichting"
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
				{{ zaak.id ? 'Opslaan' : 'Aanmaken' }}
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
	NcDateTimePicker,
} from '@nextcloud/vue'

// icons
import Plus from 'vue-material-design-icons/Plus.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

// entities
import { Zaak } from '../../entities/index.js'

export default {
	name: 'ZaakForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
		NcTextArea,
		NcDateTimePicker,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			default: false,
			required: false,
		},
		/**
		 * The id of the klant that the zaak is for.
		 * Currently there is no dropdown for selecting a klant.
		 * So it currently will pass the klantId with the zaak to the API.
		 */
		klantId: {
			type: String,
			default: null,
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
				uiterlijkeEinddatumAfdoening: null,
			},
			loading: false,
			success: null,
			error: null,
			zaakLoading: false,
			zaakType: {},
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
	mounted() {
		this.setArchiefStatusOptions()
		this.fetchZaakType()

		// If zaakItem in the store is set. apply it to zaak in this modal.
		if (zaakStore.zaakItem?.id) {
			this.zaak = {
				...this.zaak,
				...zaakStore.zaakItem,
				uiterlijkeEinddatumAfdoening: zaakStore.zaakItem.uiterlijkeEinddatumAfdoening ? new Date(zaakStore.zaakItem.uiterlijkeEinddatumAfdoening) : null,
			}
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(null)
			this?.dashboardWidget && this.$emit('close-modal')
		},
		fetchZaakType() {
			this.zaakTypeLoading = true

			zaakTypeStore.refreshZaakTypenList()
				.then(({ entities }) => {
					const selectedZaakType = entities.find((zaakType) => zaakType.id === this.zaak.zaaktype.id)

					this.zaakType = {
						options: entities.map((zaakType) => ({
							id: zaakType.id,
							label: zaakType.identificatie,
						})),
						value: selectedZaakType
							? {
								id: selectedZaakType.id,
								label: selectedZaakType.identificatie,
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
				uiterlijkeEinddatumAfdoening: this.zaak.uiterlijkeEinddatumAfdoening ? new Date(this.zaak.uiterlijkeEinddatumAfdoening).toISOString() : null,
				klant: this.klantId,
			})

			zaakStore.saveZaak(newZaak, { redirect: !this.dashboardWidget })
				.then(({ response }) => {
					this.success = response.ok

					setTimeout(() => {
						this?.dashboardWidget && this.$emit('save-success')
					}, 2000)
					setTimeout(() => {
						this.closeModal()
					}, 2500)

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
