<script setup>
import { navigationStore, rolStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef" label-id="rolForm" @close="closeModal">
		<div class="modal__content">
			<h2>Rol {{ IS_EDIT ? 'aanpassen' : 'aanmaken' }}</h2>

			<NcNoteCard v-if="success" type="success">
				<p>Rol succesvol {{ IS_EDIT ? 'aangepast' : 'aangemaakt' }}</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div v-if="success === null" class="form-group">
				<NcSelect v-bind="zaakOptions"
					v-model="zaakOptions.value"
					:disabled="loading"
					input-label="Zaak"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="rolItem.betrokkene"
					maxlength="1000"
					label="Betrokkene (url)" />

				<NcSelect
					v-bind="betrokkeneTypeOptions"
					v-model="betrokkeneTypeOptions.value"
					:disabled="loading"
					:clearable="false"
					input-label="Betrokkene Type"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="rolItem.afwijkendeNaamBetrokkene"
					maxlength="625"
					label="Afwijkende Naam Betrokkene" />

				<NcTextField
					:disabled="loading"
					:value.sync="rolItem.roltype"
					maxlength="1000"
					label="Roltype"
					required />

				<NcTextArea
					:disabled="loading"
					:value.sync="rolItem.roltoelichting"
					maxlength="1000"
					label="Roltoelichting"
					:error="!rolItem.roltoelichting" />

				<NcSelect
					v-bind="indicatieMachtigingOptions"
					v-model="indicatieMachtigingOptions.value"
					:disabled="loading"
					input-label="Indicatie Machtiging" />
			</div>

			<NcButton v-if="success === null"
				:disabled="loading
					|| !zaakOptions.value?.id
					|| !betrokkeneTypeOptions.value?.id
					|| !rolItem.roltype
					|| !rolItem.roltoelichting"
				type="primary"
				@click="editRol()">
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
import { NcButton, NcLoadingIcon, NcModal, NcTextField, NcSelect, NcTextArea, NcNoteCard } from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'RolForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcLoadingIcon,
		NcSelect,
		NcTextArea,
		NcNoteCard,
		// Icons
		ContentSaveOutline,
	},
	props: {
		zaakId: {
			type: String,
			required: false,
			default: null,
		},
		/**
		 * indicates if the modal should redirect the user to the detail page after saving
		 * @default true
		 */
		redirect: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	data() {
		return {
			rolItem: {
				zaak: '',
				betrokkene: '',
				betrokkeneType: '',
				afwijkendeNaamBetrokkene: '',
				roltype: '',
				omschrijving: '',
				omschrijvingGeneriek: '',
				roltoelichting: '',
				registratiedatum: '',
				indicatieMachtiging: '',
				contactpersoonRol: {
					emailadres: '',
					functie: '',
					telefoonnummer: '',
					naam: '',
				},
				statussen: [],
			},
			zaakOptionsLoading: false,
			zaakOptions: {
				options: [],
				value: null,
			},
			betrokkeneTypeOptions: {
				options: [
					{ label: 'Natuurlijk persoon', id: 'natuurlijk_persoon' },
					{ label: 'Niet-natuurlijk persoon', id: 'niet_natuurlijk_persoon' },
					{ label: 'Vestiging', id: 'vestiging' },
					{ label: 'Organisatorische eenheid', id: 'organisatorische_eenheid' },
					{ label: 'Medewerker', id: 'medewerker' },
				],
				value: { label: 'Natuurlijk persoon', id: 'natuurlijk_persoon' },
			},
			indicatieMachtigingOptions: {
				options: [
					{ label: 'Gemachtigde', id: 'gemachtigde' },
					{ label: 'Machtiginggever', id: 'machtiginggever' },
				],
				value: null,
			},
			// =======
			success: null,
			loading: false,
			error: null,
			IS_EDIT: false,
		}
	},
	mounted() {
		this.IS_EDIT = !!rolStore.rolItem?.id

		if (this.IS_EDIT) {
			this.rolItem = {
				...this.rolItem,
				...rolStore.rolItem,
			}

			this.indicatieMachtigingOptions.value = this.indicatieMachtigingOptions.options.find(option => option.id === this.rolItem.indicatieMachtiging)
			this.fetchData(rolStore.rolItem?.id)
		}

		this.fetchZaak(rolStore.rolItem?.zaak || this.zaakId)
	},
	methods: {
		closeModal() {
			navigationStore.setModal(null)
			rolStore.setZaakId(null)
			delete rolStore.extraData?.redirect
		},
		fetchData(id) {
			this.rolLoading = true

			rolStore.getRol(id)
				.then(({ data }) => {
					this.rolItem = {
						...this.rolItem,
						...data,
					}

					this.fetchZaak(data.zaak)
				})
				.finally(() => {
					this.rolLoading = false
				})
		},
		fetchZaak(zaakId) {
			this.zaakOptionsLoading = true

			zaakStore.refreshZakenList()
				.then(({ data }) => {
					const selectedZaak = data.find(zaak => zaak.id === zaakId)

					this.zaakOptions.options = data.map(zaak => ({
						label: zaak.identificatie,
						id: zaak.id,
					}))

					this.zaakOptions.value = selectedZaak
						? {
							label: selectedZaak.identificatie,
							id: selectedZaak.id,
						}
						: null
				})
				.finally(() => {
					this.zaakOptionsLoading = false
				})
		},
		editRol() {
			this.loading = true

			rolStore.saveRol({
				...this.rolItem,
				zaak: this.zaakOptions.value.id,
				betrokkeneType: this.betrokkeneTypeOptions.value.id,
				indicatieMachtiging: this.indicatieMachtigingOptions.value?.id || '',
			}, { redirect: this.redirect })
				.then(({ response }) => {
					this.success = response.ok

					if (response.ok) setTimeout(this.closeModal, 2500)
				})
				.catch((e) => {
					this.success = false
					this.error = e.message || 'Er is iets fout gegaan met het opslaan van de rol'
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
