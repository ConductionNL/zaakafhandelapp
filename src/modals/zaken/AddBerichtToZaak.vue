<script setup>
import { zaakStore, navigationStore, berichtStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="addBerichtToZaak"
		@close="closeModal">
		<div class="modalContent">
			<h2>Bericht toevoegen aan {{ zaakStore.zaakItem.title }}</h2>

			<div v-if="success !== null || error">
				<NcNoteCard v-if="success" type="success">
					<p>Bericht succesvol toegevoegd aan zaak</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcSelect v-bind="berichten"
					v-model="berichten.value"
					input-label="Bericht"
					:loading="berichtenLoading"
					:disabled="loading"
					required />
			</div>

			<NcButton v-if="success === null"
				:disabled="!berichten?.value || loading"
				type="primary"
				@click="addBerichtToZaak">
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
import { Zaak } from '../../entities/index.js'

import _ from 'lodash'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'AddBerichtToZaak',
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
			berichtenLoading: false,
			berichten: [],
			loading: false,
			success: null,
			error: false,
			errorCode: '',
			hasUpdated: false,
		}
	},
	mounted() {
		this.fetchBerichtenData()
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
		},
		fetchBerichtenData() {
			this.berichtenLoading = true

			berichtStore.refreshBerichtenList()
				.then(({ data }) => {
					this.berichten = {
						options: data
							.filter((bericht) => !zaakStore.zaakItem.berichten.includes(bericht.id))
							.map((bericht) => ({
								id: bericht.id,
								label: bericht.title,
							})),
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.berichtenLoading = false
				})
		},
		addBerichtToZaak() {
			this.loading = true
			this.error = false

			const zaakItemCopy = _.cloneDeep(this.zaakItem)

			if (zaakItemCopy.berichten) {
				zaakItemCopy.berichten.push(this.berichten.value.id)
			} else {
				zaakItemCopy.berichten = [this.berichten.value.id]
			}

			const newZaakItem = new Zaak(zaakItemCopy)

			zaakStore.saveZaak(newZaakItem)
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
