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
				<NcCheckboxRadioSwitch v-if="clientType === 'both'"
					:checked.sync="useMedewerkerInsteadOfKlant"
					type="switch">
					Klant / Medewerker
				</NcCheckboxRadioSwitch>

				<div>
					<NcSelect v-if="(clientType !== 'medewerker' && (clientType !== 'both' || clientType === 'both' && !useMedewerkerInsteadOfKlant))"
						v-bind="klanten"
						v-model="klanten.value"
						:user-select="true"
						input-label="Klant*"
						:loading="klantenLoading"
						:disabled="loading" />

					<NcSelect v-if="(clientType !== 'klant' && (clientType !== 'both' || clientType === 'both' && useMedewerkerInsteadOfKlant))"
						v-bind="medewerkers"
						v-model="medewerkers.value"
						:user-select="true"
						input-label="Medewerker*"
						:loading="medewerkersLoading"
						:disabled="loading" />
				</div>
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
				:disabled="loading
					|| medewerkersLoading
					|| !taakItem.title
					|| !taakItem.deadline
					|| (clientType === 'both' && !useMedewerkerInsteadOfKlant && !klanten.value?.id)
					|| (clientType === 'both' && useMedewerkerInsteadOfKlant && !medewerkers.value?.id)
					|| (clientType === 'klant' && !klanten.value?.id)
					|| (clientType === 'medewerker' && !medewerkers.value?.id)"
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
	NcCheckboxRadioSwitch,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Help from 'vue-material-design-icons/Help.vue'

/**
 * EditTaak component
 *
 * This component allows users to create and edit a taak (task).
 * It fetches data from the API and pre-selects the associated medewerker (employee) if one exists.
 * The component provides options to change the client type, pre-select a klant (client), and disable the klant dropdown.
 *
 * Props:
 * - `dashboardWidget` (Boolean, optional): Whether the modal is being used within a dashboard widget. If true, the modal will emit a 'save-success' event upon successful taak saving.
 * - `taakId` (String, optional): ID of the taak (task) to load and edit. When provided, this prop takes precedence over any ID present in `taakStore.taakItem`. The component will fetch the taak data associated with this ID from the API, load the fetched data into the form, and pre-select the associated medewerker (employee) if one exists.
 * - `clientType` (String, required): Determines which client type to use for the taak. Depending on the value, it'll show either the klant dropdown, medewerker dropdown, or both as default.
 * - `klantId` (String, optional): ID of the klant (client) to pre-select in the klanten dropdown. When provided, this prop will attempt to find and select the klant with the given ID in the klanten dropdown. If the klant ID does not exist in the dropdown options, no klant will be pre-selected.
 * - `medewerkerId` (String, optional): ID of the medewerker (employee) to pre-select in the medewerkers dropdown. When provided, this prop will attempt to find and select the medewerker with the given ID in the medewerkers dropdown. If the medewerker ID does not exist in the dropdown options, no medewerker will be pre-selected.
 */
export default {
	name: 'EditTaak',
	components: {
		NcDialog,
		NcTextField,
		NcTextArea,
		NcDateTimePicker,
		NcButton,
		NcSelect,
		NcCheckboxRadioSwitch,
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
		/**
		 * Determines which client type to use for the taak.
		 *
		 * Depending on the value, it'll show either the klant dropdown, medewerker dropdown, or both as default.
		 *
		 * Possible values:
		 * - 'klant': Only the klant (client) dropdown will be shown.
		 * - 'medewerker': Only the medewerker (employee) dropdown will be shown.
		 * - 'both': Both the klant and medewerker dropdowns will be shown.
		 *
		 * @default 'both'
		 */
		clientType: {
			type: String,
			/** @type { 'both' } */
			default: 'both',
			/**
			 * @param { 'klant' | 'medewerker' | 'both' } value - The value to validate.
			 * @return {boolean} Returns true if the value is valid, false otherwise.
			 */
			validator: (value) => ['klant', 'medewerker', 'both'].includes(value),
		},
		/**
		 * ID of the klant (client) to pre-select in the klanten dropdown.
		 *
		 * When provided, this prop will:
		 * 1. Attempt to find and select the klant with the given ID in the klanten dropdown.
		 * 2. If the klant ID does not exist in the dropdown options, no klant will be pre-selected.
		 *
		 * @example
		 * <EditTaak klantId="2469afb2-950b-48a3-a7ea-9667557c373d" />
		 */
		klantId: {
			type: String,
			default: null,
		},
		/**
		 * ID of the medewerker (employee) to pre-select in the medewerkers dropdown.
		 *
		 * When provided, this prop will:
		 * 1. Attempt to find and select the medewerker with the given ID in the medewerkers dropdown.
		 * 2. If the medewerker ID does not exist in the dropdown options, no medewerker will be pre-selected.
		 *
		 * @example
		 * <EditTaak medewerkerId="2469afb2-950b-48a3-a7ea-9667557c373d" />
		 */
		medewerkerId: {
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
			useMedewerkerInsteadOfKlant: false,
			// data
			klanten: [],
			klantenLoading: false,
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
		this.fetchData()
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
		async fetchData() {
			let taakEntity

			// if taakId is provided, fetch the taak data from the API
			if (this.taakId) {
				const { entity } = await taakStore.getTaak(this.taakId)
				taakEntity = entity
			} else if (taakStore.taakItem?.id) {
				taakEntity = taakStore.taakItem
			}

			if (taakEntity) {
				this.taakItem = {
					...this.taakItem,
					...taakEntity,
					title: taakEntity.title || '',
					type: taakEntity.type || '',
					status: taakEntity.status || 'open',
					deadline: new Date(taakEntity.deadline),
					onderwerp: taakEntity.onderwerp || '',
					toelichting: taakEntity.toelichting || '',
					klant: taakEntity?.klant || '',
					medewerker: taakEntity?.medewerker || '',
				}

				if (this.clientType === 'both') {
					this.useMedewerkerInsteadOfKlant = !!taakEntity?.medewerker
				}
			}

			if (this.clientType !== 'medewerker') this.fetchKlanten(taakEntity?.klant) // will either pass a id or undefined
			if (this.clientType !== 'klant') this.fetchMedewerkers(taakEntity?.medewerker)
		},
		/**
		 * @param {string} klantId - Optional ID of the klant to select. Will take precedence over the ID present in `taakStore.taakItem`.
		 *                           If none are provided the default selected klant will be `null`.
		 */
		fetchKlanten(klantId = null) {
			this.klantenLoading = true

			klantStore.refreshKlantenList()
				.then(({ data }) => {

					const taakKlantId = taakStore.taakItem?.klant
					const searchId = (this.klantId ?? klantId ?? taakKlantId)?.toString()

					const selectedKlant = data.find((klant) => klant?.id.toString() === searchId) || null

					this.klanten = {
						options: data.map((klant) => ({
							id: klant.id,
							displayName: `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}`,
							subName: klant.email,
							icon: klant.icon ?? '',

						})),
						value: selectedKlant
							? {
								id: selectedKlant?.id,
								displayName: `${selectedKlant.voornaam} ${selectedKlant.tussenvoegsel} ${selectedKlant.achternaam}`,
								subName: selectedKlant.email,
								icon: selectedKlant.icon ?? '',
							}
							: null,
					}
				})
				.catch((err) => {
					console.error(err)
				})
				.finally(() => {
					this.klantenLoading = false
				})
		},
		/**
		 * @param {string} medewerkerId - Optional ID of the medewerker to select. Will take precedence over the ID present in `taakStore.taakItem`.
		 *                                If none are provided the default selected medewerker will be `null`.
		 */
		fetchMedewerkers(medewerkerId = null) {
			medewerkerStore.refreshMedewerkersList()
				.then(({ data }) => {

					const taakMedewerkerId = taakStore.taakItem?.medewerker
					const searchId = (this.medewerkerId ?? medewerkerId ?? taakMedewerkerId)?.toString()

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

			let klantId
			if (this.clientType !== 'medewerker' && (this.clientType !== 'both' || !this.useMedewerkerInsteadOfKlant)) {
				klantId = this.klanten.value?.id
			}

			let medewerkerId
			if (this.clientType !== 'klant' && (this.clientType !== 'both' || this.useMedewerkerInsteadOfKlant)) {
				medewerkerId = this.medewerkers.value?.id
			}

			taakStore.saveTaak({
				...this.taakItem,
				klant: klantId || null,
				medewerker: medewerkerId || null,
				status: this.taakItem.status === 'gesloten' ? 'gesloten' : 'open',
				deadline: this.taakItem.deadline ? this.taakItem.deadline.toISOString() : null,
			})
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
