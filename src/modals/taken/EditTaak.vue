<script setup>
import { taakStore, navigationStore, klantStore, medewerkerStore } from '../../store/store.js'
</script>

<template>
	<NcDialog
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

			<div>
				<p>Deadline</p>
				<NcDateTimePicker
					v-model="taakItem.deadline"
					:disabled="loading"
					required />
			</div>

			<NcTextField
				:disabled="loading"
				:value.sync="taakItem.onderwerp"
				label="Onderwerp"
				maxlength="255" />

			<NcTextArea
				:disabled="loading"
				:value.sync="taakItem.toelichting"
				label="Toelichting" />

			<div>
				<NcSelect
					v-bind="medewerkers"
					v-model="medewerkers.value"
					:user-select="true"
					input-label="Medewerker"
					:loading="medewerkersLoading"
					:disabled="loading" />
			</div>
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
				:disabled="loading || medewerkersLoading || !taakItem.title || !taakItem.deadline"
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
	NcDateTimePicker,
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
		NcDateTimePicker,
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
		/**
		 * Whether the modal is being used within a dashboard widget.
		 *
		 * If true, the modal will emit a 'save-success' event upon successful taak saving.
		 */
		dashboardWidget: {
			type: Boolean,
			required: false,
		},
		/**
		 * ID of a medewerker (employee) to pre-select in the dropdown.
		 *
		 * This prop has the highest priority when determining which medewerker to select:
		 * 1. selectedMedewerker prop (if provided)
		 * 2. medewerker ID from taakId prop fetch result (if taakId provided)
		 * 3. taakStore.taakItem.klant
		 *
		 * Note: While functional, using this prop is not recommended. Instead, prefer using
		 * the taakId prop to load a complete taak, or let the component handle medewerker
		 * selection based on the taakStore state.
		 *
		 * If the provided klant ID does not exist in the database, or is in any other way invalid,
		 * the medewerker dropdown will not have a default/selected option.
		 */
		selectedMedewerker: {
			type: String,
			default: null,
		},
		/**
		 * ID of the taak (task) to load and edit.
		 *
		 * When provided, this prop takes precedence over any ID present in `taakStore.taakItem`.
		 * The component will:
		 * 1. Fetch the taak data associated with this ID from the API
		 * 2. Load the fetched data into the form, overwriting any existing taak data in the modal
		 * 3. Pre-select the associated medewerker (employee) if one exists
		 *
		 * @example
		 * <EditTaak taakId="2469afb2-950b-48a3-a7ea-9667557c373d" />
		 * <EditTaak :taakId="taakStore.taakItem?.id" />
		 */
		taakId: {
			type: String,
			default: null,
		},
	},
	data() {
		return {
			// state
			success: false,
			loading: false,
			error: false,
			// data
			medewerkers: [],
			medewerkersLoading: false,
			// item
			taakItem: {
				title: '',
				type: '',
				status: '',
				deadline: new Date(),
				onderwerp: '',
				toelichting: '',
			},
			executorOptions: {
				options: [
					{
						id: 'klant',
						label: 'Klant',
					},
					{
						id: 'medewerker',
						label: 'Medewerker',
					},
				],
				value: {
					id: 'klant',
					label: 'Klant',
				},
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
		if (this.taakId) {
			this.fetchTaakData(this.taakId)
		} else if (taakStore.taakItem?.id) {
			// fetchMedewerkers also gets called by fetchTaakData, if taakId is provided. So we don't need to call it first
			this.fetchMedewerkers()

			this.taakItem = {
				...taakStore.taakItem,
				title: taakStore.taakItem.title || '',
				type: taakStore.taakItem.type || '',
				status: taakStore.taakItem.status || 'open',
				deadline: new Date(taakStore.taakItem.deadline),
				onderwerp: taakStore.taakItem.onderwerp || '',
				toelichting: taakStore.taakItem.toelichting || '',
				klant: klantStore.klantItem?.id || '',
			}
		} else {
			this.fetchMedewerkers()
		}
	},
	methods: {
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		closeModal() {
			navigationStore.setModal(null)

			if (this.dashboardWidget === true) {
				this.$emit('close-modal')
			}
		},
		async fetchTaakData(id) {
			const { entity: taakEntity } = await taakStore.getTaak(id)

			this.taakItem = {
				...taakEntity,
				deadline: new Date(taakEntity.deadline),
			}

			this.fetchMedewerkers(taakEntity?.klant)
		},
		/**
		 * @param {string} medewerkerId - Optional ID of the medewerker to select. Will take precedence over the ID present in `taakStore.taakItem`.
		 *                                If none are provided the default selected medewerker will be `null`.
		 */
		fetchMedewerkers(medewerkerId = null) {
			medewerkerStore.refreshMedewerkersList()
				.then(({ data }) => {

					const taakKlantId = taakStore.taakItem?.klant
					const searchId = (this.selectedMedewerker ?? medewerkerId ?? taakKlantId)?.toString()

					const selectedMedewerker = data.find((medewerker) => medewerker?.id.toString() === searchId) || null

					this.medewerkers = {
						options: data.map((medewerker) => ({
							id: medewerker.id,
							displayName: `${medewerker.voornaam} ${medewerker.tussenvoegsel} ${medewerker.achternaam}`,
							subName: medewerker.email,
							icon: medewerker.icon ?? '',

						})),
						value: selectedMedewerker
							? {
								id: selectedMedewerker?.id,
								displayName: `${selectedMedewerker.voornaam} ${selectedMedewerker.tussenvoegsel} ${selectedMedewerker.achternaam}`,
								subName: selectedMedewerker.email,
								icon: selectedMedewerker.icon ?? '',
							}
							: null,
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.medewerkersLoading = false
				})
		},
		async editTaak() {
			this.loading = true

			taakStore.saveTaak({
				...this.taakItem,
				klant: this.medewerkers.value?.id ?? '',
				status: this.taakItem.status === 'gesloten' ? 'gesloten' : 'open',
				deadline: this.taakItem.deadline ? this.taakItem.deadline.toISOString() : null,
			}, { doNotRefresh: true })
				.then(({ response }) => {
					this.success = response.ok
					setTimeout(this.closeModal, 2000)

					if (this.dashboardWidget === true && response.ok) {
						this.$emit('save-success')
					}
				})
				.catch((err) => {
					this.error = err.message || 'An error occurred while saving the taak'
				})
				.finally(() => {
					this.loading = false
				})
		},
		openLink(url, target) {
			window.open(url, target)
		},
	},
}
</script>
