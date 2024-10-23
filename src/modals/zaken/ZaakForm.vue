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
			}
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(null)
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
			}
		},
		fetchZaakType() {
			this.zaakTypeLoading = true

			zaakTypeStore.refreshZaakTypeList()
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
			})

			zaakStore.saveZaak(newZaak)
				.then(({ response }) => {
					this.success = response.ok
					setTimeout(this.closeModal, 2500)
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
