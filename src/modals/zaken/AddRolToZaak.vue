<script setup>
import { zaakStore, navigationStore, rolStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef"
		label-id="addRolToZaak"
		@close="closeModal">
		<div class="modalContent">
			<h2>Rol toevoegen aan {{ zaakStore.zaakItem.title }}</h2>

			<div v-if="success !== null || error">
				<NcNoteCard v-if="success" type="success">
					<p>Rol succesvol toegevoegd aan zaak</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>

			<div v-if="success === null" class="form-group">
				<NcSelect v-bind="rollen"
					v-model="rollen.value"
					input-label="Rol"
					:loading="rollenLoading"
					:disabled="loading"
					required />
			</div>

			<NcButton v-if="success === null"
				:disabled="!rollen?.value || loading"
				type="primary"
				@click="addRolToZaak">
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
import { Rol } from '../../entities/index.js'

import _ from 'lodash'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'AddRolToZaak',
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
			rollenLoading: false,
			rollen: [],
			loading: false,
			success: null,
			error: false,
			errorCode: '',
			hasUpdated: false,
		}
	},
	mounted() {
		this.fetchRollenData()
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
		},
		fetchRollenData() {
			this.rollenLoading = true

			rolStore.refreshRollenList()
				.then(({ data }) => {
					this.rollen = {
						options: data
							// zaak is stored on the rol itself as a singular id, indicating that only rollen without a zaak can be used
							.filter((rol) => !rol.zaak)
							.map((rol) => ({
								id: rol.id,
								label: rol.title,
							})),
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.rollenLoading = false
				})
		},
		addRolToZaak() {
			this.loading = true
			this.error = false

			const rolItem = rolStore.rollenList.find((rol) => rol.id === this.rollen.value.id)
			if (!rolItem) {
				this.error = 'something went majorly wrong'
				this.loading = false
				return
			}

			const rolItemCopy = _.cloneDeep(rolItem)

			rolItemCopy.zaak = zaakStore.zaakItem.id

			const newRolItem = new Rol(rolItemCopy)

			rolStore.saveRol(newRolItem)
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
