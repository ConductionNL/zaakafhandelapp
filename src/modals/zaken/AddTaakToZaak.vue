<script setup>
import { zaakStore, navigationStore, taakStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef" label-id="addTaakToZaak" @close="closeModal">
		<div class="modalContent">
			<h2>Taak toevoegen aan {{ zaakStore.zaakItem.title }}</h2>

			<div v-if="success !== null || error">
				<NcNoteCard v-if="success" type="success">
					<p>Taak succesvol toegevoegd aan zaak</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcSelect v-bind="taken"
					v-model="taken.value"
					input-label="Taak"
					:loading="takenLoading"
					:disabled="loading"
					required />
			</div>

			<NcButton v-if="success === null"
				:disabled="!taken?.value || loading"
				type="primary"
				@click="addTaakToZaak">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Plus v-if="!loading" :size="20" />
				</template>
				Toevoegen
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcLoadingIcon, NcNoteCard, NcSelect } from '@nextcloud/vue'
import { Taak } from '../../entities/index.js'

import _ from 'lodash'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'AddTaakToZaak',
	components: {
		NcModal,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcSelect,
		// Icons
		Plus,
	},
	data() {
		return {
			takenLoading: false,
			taken: [],
			loading: false,
			success: null,
			error: false,
			errorCode: '',
			hasUpdated: false,
		}
	},
	mounted() {
		this.zaakItem = zaakStore.zaakItem
		this.fetchTakenData()
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
		},
		fetchTakenData() {
			this.takenLoading = true

			taakStore.refreshTakenList()
				.then(({ data }) => {
					this.taken = {
						options: data
							// zaak is stored on the taak itself as a singular id, indicating that only taken without a zaak can be used
							.filter((taak) => !taak.zaak)
							.map((taak) => ({
								id: taak.id,
								label: taak.title,
							})),
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.takenLoading = false
				})
		},
		addTaakToZaak() {
			this.loading = true
			this.error = false

			const taakItem = taakStore.takenList.find((taak) => taak.id === this.taken.value.id)
			if (!taakItem) {
				this.error = 'something went majorly wrong'
				this.loading = false
				return
			}

			const taakItemCopy = _.cloneDeep(taakItem)

			taakItemCopy.zaak = this.zaakItem.id

			const newTaakItem = new Taak(taakItemCopy)

			taakStore.saveTaak(newTaakItem)
				.then(({ response }) => {
					this.success = response.ok

					// Wait for the user to read the feedback then close the model
					const self = this
					setTimeout(function() {
						self.success = null
						self.closeModal()
					}, 2000)

					this.hasUpdated = false
				})
				.catch((err) => {
					this.error = err
					this.hasUpdated = false
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
