<script setup>
import { navigationStore, besluitStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="zaakForm"
		@close="closeModal">
		<div class="modalContent">
			<h2>Besluit {{ IS_EDIT ? 'aanpassen' : 'aanmaken' }}</h2>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>Besluit succesvol {{ IS_EDIT ? 'aangepast' : 'aangemaakt' }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null">
				<p>Selecteer een zaak om het besluit aan te maken.</p>
				<NcSelect v-bind="zaak"
					v-model="zaak.value"
					input-label="Zaak"
					:loading="zaakLoading"
					:disabled="zaakLoading"
					required />

				<div class="form-group">
					<NcTextField :disabled="zaakLoading"
						label="Besluit"
						maxlength="1000"
						:value.sync="besluit.besluit"
						required />
				</div>
			</div>

			<NcButton v-if="success === null"
				:disabled="loading
					|| !zaak.value?.id
					|| !besluit.besluit"
				type="primary"
				@click="saveBesluit()">
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
	NcLoadingIcon,
	NcSelect,
} from '@nextcloud/vue'

// icons
import Plus from 'vue-material-design-icons/Plus.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

// entities
import { Besluit } from '../../entities/index.js'

export default {
	name: 'BesluitForm',
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
	},
	data() {
		return {
			besluit: {
				besluit: '',
				zaak: '',
			},
			IS_EDIT: false,
			zaak_id: null,
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
		if (besluitStore.besluitItem?.id) {
			this.IS_EDIT = true
			this.besluit = {
				...this.besluit,
				...besluitStore.besluitItem,
			}
		}

		this.fetchZaak()
	},
	methods: {
		closeModal() {
			navigationStore.setModal(null)
			besluitStore.zaakId = null
			this.dashboardWidget && this.$emit('close-modal')
		},
		fetchZaak() {
			this.zaakLoading = true

			zaakStore.refreshZakenList()
				.then(({ entities }) => {
					const compareId = this.besluit?.zaak || besluitStore.zaakId
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
		saveBesluit() {
			this.loading = true

			const newBesluit = new Besluit({
				...this.besluit,
			})

			besluitStore.saveBesluit({
				...newBesluit,
				zaak: this.zaak.value.id,
			})
				.then(({ response }) => {
					this.success = response.ok
					setTimeout(this.closeModal, 2500)

					this.dashboardWidget && this.$emit('save-success')
				})
				.catch((err) => {
					console.error(err)
					this.error = err.message || 'Er is iets fout gegaan bij het opslaan van het besluit.'
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
