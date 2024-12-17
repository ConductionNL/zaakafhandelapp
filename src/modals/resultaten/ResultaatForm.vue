<script setup>
import { navigationStore, resultaatStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="zaakForm"
		@close="closeModal">
		<div class="modalContent">
			<h2>Resultaat {{ IS_EDIT ? 'aanpassen' : 'aanmaken' }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Resultaat succesvol {{ IS_EDIT ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcSelect v-bind="zaak"
					v-model="zaak.value"
					input-label="Zaak"
					:loading="zaakLoading"
					:disabled="zaakLoading"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Resultaat type"
					maxlength="1000"
					:value.sync="resultaat.resultaattype"
					required />
				<NcTextField :disabled="zaakLoading"
					label="Toelichting"
					maxlength="255"
					:value.sync="resultaat.toelichting" />
			</div>

			<NcButton v-if="success === null"
				:disabled="loading
					|| !zaak.value?.id
					|| !resultaat.resultaattype"
				type="primary"
				@click="saveResultaat()">
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
	NcNoteCard,
	NcButton,
	NcTextField,
	NcSelect,
	NcLoadingIcon,
} from '@nextcloud/vue'

// icons
import Plus from 'vue-material-design-icons/Plus.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

// entities
import { Resultaat } from '../../entities/index.js'

export default {
	name: 'ResultaatForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			default: false,
			required: false,
		},
		/**
		 * The id of the zaak that the resultaat is for.
		 */
		zaakId: {
			type: String,
			default: null,
		},
	},
	data() {
		return {
			resultaat: {
				url: '',
				zaak: '',
				resultaattype: '',
				toelichting: '',
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
		// If zaakItem in the store is set. apply it to zaak in this modal.
		if (resultaatStore.resultaatItem?.id) {
			this.IS_EDIT = true
			this.resultaat = {
				...this.resultaat,
				...resultaatStore.resultaatItem,
			}
		}

		this.fetchZaak()
	},
	methods: {
		closeModal() {
			navigationStore.setModal(null)
			resultaatStore.zaakId = null
			this.dashboardWidget && this.$emit('close-modal')
		},
		fetchZaak() {
			this.zaakLoading = true

			zaakStore.refreshZakenList()
				.then(({ entities }) => {
					// the priority list of id's to find is zaakId inside resultaat (edit modal only), zaakId prop, and zaakId set in store (used when creating a new resultaat)
					const idToFind = this.resultaat.zaak || this.zaakId || resultaatStore.zaakId
					const selectedZaak = entities.find((zaak) => zaak.id === idToFind)

					this.zaak = {
						options: entities.map((zaak) => ({
							id: zaak.id,
							label: zaak.identificatie,
						})),
						value: selectedZaak
							? {
								id: selectedZaak.id,
								label: selectedZaak.identificatie,
							  }
							: null,
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.zaakLoading = false
				})
		},
		saveResultaat() {
			this.loading = true

			const newResultaat = new Resultaat({
				...this.resultaat,
				zaak: this.zaak.value?.id || null,
			})

			resultaatStore.saveResultaat(newResultaat)
				.then(({ response }) => {
					this.success = response.ok
					setTimeout(this.closeModal, 2500)

					this.dashboardWidget && this.$emit('save-success')
				})
				.catch((err) => {
					console.error(err)
					this.error = err.message || 'Er is iets fout gegaan bij het opslaan van het resultaat.'
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
