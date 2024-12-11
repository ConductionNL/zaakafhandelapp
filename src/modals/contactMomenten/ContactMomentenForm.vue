<script setup>
import { contactMomentStore, navigationStore, taakStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcDialog name="Contactmoment"
		size="large"
		label-id="contactMomentenForm"
		dialog-classes="ContactMomentenForm"
		:close-on-click-outside="false"
		@closing="closeModalFromButton()">
		<NcNoteCard v-if="success" type="success">
			<p>Contactmoment succesvol opgeslagen</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success">
			<div class="headerContainer">
				<div class="personInfoContainer">
					<NcNoteCard type="info" class="noteCard">
						<template #default>
							<div v-if="klant">
								{{ `${getSex(klant)} ${getName(klant)}` }}
								<div v-if="klant?.type === 'persoon'" class="flexContainer">
									<div>
										Geboortedatum: {{ getValidISOstring(klant?.geboortedatum) ? new Date(klant?.geboortedatum).toLocaleDateString() : 'N/A' }}
									</div>
									<div>
										Geboorteplaats: {{ klant?.plaats ?? 'N/A' }}
									</div>
								</div>
								<div v-if="klant?.type === 'organisatie'" class="flexContainer">
									<div>
										KVK: {{ klant?.kvkNummer ?? 'N/A' }}
									</div>
									<div>
										Locatie: {{ klant?.postcode ?? 'N/A' }} {{ klant?.straatnaam ?? 'N/A' }}
									</div>
								</div>
							</div>
							<div v-else>
								Geen klant geselecteerd
							</div>
						</template>
					</NcNoteCard>
				</div>
				<div v-if="!klant" class="buttonsContainer">
					<div>
						<NcButton
							:disabled="loading"
							:loading="fetchLoading"
							type="primary"
							@click="openSearchKlantModal('persoon')">
							<template #icon>
								<Plus :size="20" />
							</template>
							Persoon zoeken
						</NcButton>
					</div>
					<div class="orContainer">
						of
					</div>
					<div>
						<NcButton
							:disabled="loading"
							:loading="fetchLoading"
							type="primary"
							@click="openSearchKlantModal('organisatie')">
							<template #icon>
								<Plus :size="20" />
							</template>
							Organisatie zoeken
						</NcButton>
					</div>
				</div>

				<div v-if="klant && !isView" class="buttonsContainer">
					<div>
						<NcButton
							:disabled="loading"
							:loading="fetchLoading"
							type="primary"
							@click="klant = null">
							<template #icon>
								<Minus :size="20" />
							</template>
							Klant ontkoppelen
						</NcButton>
					</div>
				</div>
				<div v-if="isView" class="statusContainer">
					<div v-if="contactMoment.status">
						Status: {{ contactMoment.status }}
					</div>
					<div v-if="contactMoment.startDate">
						Start datum: {{ new Date(contactMoment.startDate).toLocaleDateString() }}
					</div>
				</div>
			</div>

			<div v-if="!success" class="form-group">
				<NcTextArea :value.sync="contactMoment.notitie"
					label="Notitie"
					:disabled="loading"
					:loading="fetchLoading"
					placeholder="Notitie" />
			</div>
			<div class="tabContainer">
				<BTabs content-class="mt-3" justified>
					<BTab :title="`Zaken ${klant ? (zaken.length ? `(${zaken.length})` : '(0)') : ''}`">
						<div v-if="zaken.length">
							<NcListItem v-for="(zaak, key) in zaken"
								:key="key"
								:name="zaak.identificatie"
								:bold="false"
								:details="zaak.startdatum"
								:disabled="loading"
								:loading="fetchLoading"
								:active="selectedZaak === zaak.id"
								:force-display-actions="true"
								@click="setSelectedZaak(zaak.id)">
								<template #icon>
									<BriefcaseAccountOutline :size="44" />
								</template>
								<template #subname>
									{{ zaak.omschrijving }}
								</template>
							</NcListItem>
						</div>
						<NcEmptyContent v-else icon="icon-folder" title="Geen zaken gevonden">
							<template #description>
								Er zijn geen zaken gevonden voor deze klant.
							</template>
						</NcEmptyContent>
					</BTab>
					<BTab :title="`Taken ${klant ? (taken.length ? `(${taken.length})` : '(0)') : ''}`">
						<div v-if="taken.length">
							<NcListItem v-for="(taak, key) in taken"
								:key="key"
								:name="taak.title"
								:bold="false"
								:details="taak.status"
								:disabled="loading"
								:loading="fetchLoading"
								:active="selectedTaak === taak.id"
								:force-display-actions="true"
								@click="setSelectedTaak(taak.id)">
								<template #icon>
									<CalendarMonthOutline :size="44" />
								</template>

								<template #subname>
									{{ taak.omschrijving }}
								</template>
							</NcListItem>
						</div>
						<NcEmptyContent v-else icon="icon-tasks" title="Geen taken gevonden">
							<template #description>
								Er zijn geen taken gevonden voor deze klant.
							</template>
						</NcEmptyContent>
					</BTab>
					<BTab :title="`Producten ${klant ? (klant?.producten?.length ? `(${klant?.producten?.length})` : '(0)') : ''}`">
						<div v-if="klant?.producten?.length">
							<NcListItem v-for="(product, key) in klant.producten"
								:key="key"
								:name="product.naam ?? 'N/A'"
								:bold="false"
								:details="product.omschrijving ?? 'N/A'"
								:disabled="loading"
								:loading="fetchLoading"
								:active="selectedProduct === product.id"
								:force-display-actions="true"
								@click="setSelectedProduct(product.id)">
								<template #icon>
									<BriefcaseAccountOutline :size="44" />
								</template>
							</NcListItem>
						</div>
						<NcEmptyContent v-else icon="icon-folder" title="Geen producten gevonden">
							<template #description>
								Er zijn geen producten gevonden voor deze klant.
							</template>
						</NcEmptyContent>
					</BTab>
				</BTabs>
			</div>

			<SearchKlantModal v-if="searchKlantModalOpen"
				:dashboard-widget="true"
				:starting-type="startingType"
				@selected-klant="fetchKlantData($event)"
				@close-modal="closeSearchKlantModal" />
		</div>
		<template #actions>
			<NcButton
				:disabled="loading || success"
				type="secondary"
				@click="closeModal()">
				<template #icon>
					<Cancel :size="20" />
				</template>
				Annuleer
			</NcButton>
			<NcActions v-if="!isView"
				:disabled="loading || success || fetchLoading"
				:primary="true"
				menu-name="Acties">
				<template #icon>
					<DotsHorizontal :size="20" />
				</template>
				<NcActionButton @click="openTaakForm('medewerker')">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
					Medewerker taak aanmaken
				</NcActionButton>
				<NcActionButton @click="openTaakForm('klant')">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
					Klant taak aanmaken
				</NcActionButton>
				<NcActionButton :disabled="true" @click="zaakStore.setZaakItem(); navigationStore.setModal('editZaak')">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
					Zaak starten
				</NcActionButton>
				<NcActionButton :disabled="contactMoment.status === 'gesloten'" @click="() => (contactMoment.status = 'gesloten')">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
					Sluit Contactmoment
				</NcActionButton>
			</NcActions>
			<NcButton
				v-if="!isView"
				type="primary"
				:disabled="!klant || loading || success || fetchLoading"
				:loading="loading"
				@click="addContactMoment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-else :size="20" />
				</template>
				{{ isEdit ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</template>

		<EditTaakForm v-if="taakFormOpen"
			:dashboard-widget="true"
			:client-type="taakClientType"
			:klant-id="klant?.id"
			@close-modal="closeTaakForm"
			@save-success="closeTaakForm" />
	</NcDialog>
</template>

<script>
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcButton, NcActions, NcLoadingIcon, NcDialog, NcTextArea, NcNoteCard, NcListItem, NcActionButton, NcEmptyContent } from '@nextcloud/vue'

// Forms
import SearchKlantModal from '../../modals/klanten/SearchKlantModal.vue'
import EditTaak from '../../modals/taken/EditTaak.vue'

// Icons
import Plus from 'vue-material-design-icons/Plus.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Minus from 'vue-material-design-icons/Minus.vue'
import getValidISOstring from '../../services/getValidISOstring.js'

export default {
	name: 'ContactMomentenForm',
	components: {
		NcDialog,
		NcTextArea,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcListItem,
		NcActionButton,
		NcEmptyContent,
		EditTaakForm: EditTaak,
		// Icons
		Plus,
		BriefcaseAccountOutline,
		CalendarMonthOutline,
		DotsHorizontal,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			default: false,
		},
		contactMomentId: {
			type: String,
			default: null,
		},
		/**
		 * If true, the form is in view mode and no actions are shown
		 */
		isView: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			/**
			 * determines if this modal is an edit modal or a create modal. Is unrelated to the isView property.
			 */
			isEdit: !!this.contactMomentId,
			fetchLoading: false, // used as the loading state when editing
			success: false,
			loading: false,
			error: false,
			contactMoment: {
				titel: '',
				notitie: '',
				status: 'open',
				startDate: null,
			},
			klantenLoading: false,
			zaken: [],
			taken: [],
			berichten: [],
			contactMomenten: [],
			auditTrails: [],
			klant: null,
			searchKlantModalOpen: false,
			selectedZaak: null,
			selectedTaak: null,
			selectedProduct: null,
			startingType: 'all',
			taakFormOpen: false,
			taakClientType: 'both',
		}
	},
	mounted() {
		if (this.contactMomentId) {
			this.fetchData(this.contactMomentId)
		}
	},
	methods: {
		async fetchData(id) {
			this.fetchLoading = true

			const { data } = await contactMomentStore.getContactMoment(id)

			this.contactMoment = {
				...data,
				titel: data.titel ?? '',
				notitie: data.notitie ?? '',
				status: data.status ?? null,
				startDate: data.startDate ?? null,
			}

			this.selectedZaak = data.zaak
			this.selectedTaak = data.taak
			this.selectedProduct = data.product

			await this.fetchKlantData(data.klant)

			this.fetchLoading = false
		},

		// Modal functions
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		closeModal() {
			navigationStore.setModal(false)

			this.$emit('close-modal')
		},

		// Contactmoment functions
		addContactMoment() {
			this.loading = true

			const endpoint = this.contactMomentId ? `contactmomenten/${this.contactMomentId}` : 'contactmomenten'
			const method = this.contactMomentId ? 'PUT' : 'POST'

			fetch(
				`/index.php/apps/zaakafhandelapp/api/${endpoint}`,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						...this.contactMoment,
						notitie: this.contactMoment.notitie,
						klant: this.klant?.id ?? '',
						zaak: this.selectedZaak ?? '',
						taak: this.selectedTaak ?? '',
						product: this.selectedProduct ?? '',
						status: this.contactMoment.status === 'gesloten' ? 'gesloten' : 'open',
						startDate: new Date().toISOString(),
					}),
				},
			)
				.then((response) => {
					this.succesMessage = true
					// Get the modal to self close
					this.success = true
					this.loading = false

					setTimeout(this.closeModal, 2000)
					if (this.dashboardWidget === true) {
						this.$emit('save-success')
					}
				})
				.catch((err) => {
					console.error(err)
				})
		},

		// Search functions
		openSearchKlantModal(type) {
			this.searchKlantModalOpen = true
			this.startingType = type
		},

		// Klant functions
		closeSearchKlantModal() {
			this.searchKlantModalOpen = false
		},

		openTaakForm(clientType = 'both') {
			this.taakFormOpen = true
			this.taakClientType = clientType
			taakStore.setTaakItem(null)
		},

		closeTaakForm() {
			this.taakFormOpen = false
		},

		async fetchKlantData(id) {
			try {
				const klantResponse = await fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}`)
				this.klant = await klantResponse.json()

				// fetch all data in parallel
				const [zakenRes, takenRes, berichtenRes, auditTrailRes] = await Promise.all([
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/zaken`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/taken`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/berichten`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/audit_trail`),
				])

				// parse all data
				const [zakenData, takenData, berichtenData, auditTrailData] = await Promise.all([
					zakenRes.json(),
					takenRes.json(),
					berichtenRes.json(),
					auditTrailRes.json(),
				])

				// set data
				if (Array.isArray(zakenData.results)) {
					this.zaken = zakenData.results
				}
				if (Array.isArray(takenData.results)) {
					this.taken = takenData.results
				}
				if (Array.isArray(berichtenData.results)) {
					this.berichten = berichtenData.results
				}
				if (Array.isArray(auditTrailData)) {
					this.auditTrails = auditTrailData
				}

			} catch (error) {
				console.error('Error in fetchKlantData:', error)
				throw error
			}
		},

		getName(klant) {
			if (klant.type === 'persoon') {
				return `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}` ?? 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.bedrijfsnaam ?? 'onbekend'
			}
			return 'onbekend'
		},
		getSex(klant) {
			if (klant.type === 'persoon') {
				return `(${klant?.geslacht})`
			}
			return ''
		},

		// Tabs
		setSelectedZaak(zaak) {
			if (this.selectedZaak === zaak) {
				this.selectedZaak = null
			} else { this.selectedZaak = zaak }
		},

		setSelectedTaak(taak) {
			if (this.selectedTaak === taak) {
				this.selectedTaak = null
			} else { this.selectedTaak = taak }
		},

		setSelectedProduct(product) {
			if (this.selectedProduct === product) {
				this.selectedProduct = null
			} else { this.selectedProduct = product }
		},
	},
}
</script>

<style>
div[class='modal-container']:has(.ContactMomentenForm) {
	width: clamp(1000px, 100%, 1200px) !important;
}
</style>

<style scoped>
.rolDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}

.headerContainer {
    display: flex;
    gap: 200px;
}

.noteCard {
    min-width: 350px;
}

.orContainer {
    margin-inline: var(--zaa-margin-10);
}

.buttonsContainer {
    display: flex;
    align-items: center;
}

.form-group {
    margin-block-end: 100px;
}

.tabContainer {
    margin-block-end: var(--zaa-margin-20);
}

.modalButtonsContainer {
    display: flex;
    justify-content: flex-end;
    gap: var(--zaa-margin-10);
}

.flexContainer, .statusContainer {
	display: flex;
	gap: var(--zaa-margin-10);
}

.statusContainer {
	align-items: center;
}
</style>
