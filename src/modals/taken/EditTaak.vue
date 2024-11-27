<script setup>
import { taakStore, navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcDialog v-if="navigationStore.modal === 'editTaak'"
		name="Taak"
		size="normal"
		@closing="closeModalFromButton()">
		<NcNoteCard v-if="success" type="success">
			<p>Taak succesvol aangepast</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success" class="form-group">
			<NcTextField
				:disabled="loading"
				:value.sync="taakItem.title"
				required
				label="Titel"
				maxlength="255" />

			<NcTextField
				:disabled="loading"
				:value.sync="taakItem.type"
				label="Type"
				maxlength="255" />

			<NcSelect
				v-bind="statusOptions"
				v-model="taakItem.status"
				:disabled="loading"
				input-label="Status"
				required />

			<NcTextField
				:disabled="loading"
				:value.sync="taakItem.onderwerp"
				label="Onderwerp"
				maxlength="255" />

			<NcTextArea
				:disabled="loading"
				:value.sync="taakItem.toelichting"
				label="Toelichting" />

			<NcSelect
				v-bind="klanten"
				v-model="klanten.value"
				input-label="Klant"
				:loading="klantenLoading"
				:disabled="loading" />
		</div>

		<template #actions>
			<NcButton @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success ? 'Sluiten' : 'Annuleer' }}
			</NcButton>
			<NcButton @click="openLink('https://conduction.gitbook.io/opencatalogi-nextcloud/gebruikers/publicaties', '_blank')">
				<template #icon>
					<Help :size="20" />
				</template>
				Help
			</NcButton>
			<NcButton v-if="!success"
				:disabled="loading || klantenLoading || !taakItem.title"
				type="primary"
				@click="editTaak()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading && taakStore.taakItem?.id" :size="20" />
					<Plus v-if="!loading && !taakStore.taakItem?.id" :size="20" />
				</template>
				{{ taakStore.taakItem?.id ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import {
	NcButton,
	NcDialog,
	NcTextField,
	NcTextArea,
	NcSelect,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Help from 'vue-material-design-icons/Help.vue'

export default {
	name: 'EditTaak',
	components: {
		NcDialog,
		NcTextField,
		NcTextArea,
		NcButton,
		NcSelect,
		NcLoadingIcon,
		NcNoteCard,
		// Icons
		ContentSaveOutline,
		Cancel,
		Plus,
		Help,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			required: false,
		},
	},
	data() {
		return {
			success: false,
			loading: false,
			error: false,
			hasUpdated: false,
			klantenLoading: false,
			klanten: [],
			taakItem: {
				title: '',
				type: '',
				status: '',
				onderwerp: '',
				toelichting: '',
			},
			statusOptions: {
				options: [
					{
						id: 'status1',
						label: 'Status 1',
					},
					{
						id: 'status2',
						label: 'Status 2',
					},
					{
						id: 'status3',
						label: 'Status 3',
					},
				],
			},
		}
	},
	mounted() {
		this.fetchKlanten()
	},
	updated() {
		if (navigationStore.modal === 'editTaak' && !this.hasUpdated) {
			if (taakStore.taakItem?.id) {
				this.taakItem = {
					...taakStore.taakItem,
					title: taakStore.taakItem.title || '',
					type: taakStore.taakItem.type || '',
					status: taakStore.taakItem.status || '',
					onderwerp: taakStore.taakItem.onderwerp || '',
					toelichting: taakStore.taakItem.toelichting || '',
					klant: klantStore.klantItem?.id || '',
				}
				this.fetchKlanten()
			} else {
				this.taakItem.klant = klantStore.klantItem?.id || ''
				this.fetchKlanten()
			}
			this.hasUpdated = true
		}
	},
	methods: {
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		closeModal() {
			navigationStore.setModal(false)
			this.success = false
			this.loading = false
			this.error = false
			this.hasUpdated = false
			this.taakItem = {
				title: '',
				type: '',
				status: '',
				onderwerp: '',
				toelichting: '',
				klant: '',
			}
		},
		fetchKlanten() {
			this.klantenLoading = true

			klantStore.refreshKlantenList()
				.then(({ response, data }) => {
					const selectedKlant = data.filter((klant) => klant?.id.toString() === taakStore.taakItem?.klant?.toString())[0] || null

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
		async editTaak() {
			this.loading = true
			try {
				await taakStore.saveTaak({
					...this.taakItem,
					klant: this.klanten.value?.id ?? '',
					status: this.taakItem.status?.id ?? null,
				}, this.dashboardWidget)
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)
				if (this.dashboardWidget === true) {
					this.$emit('save-success')
				}
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the taak'
			}
		},
		openLink(url, target) {
			window.open(url, target)
		},
	},
}
</script>
