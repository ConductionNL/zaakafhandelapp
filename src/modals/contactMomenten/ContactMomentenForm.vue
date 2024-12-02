<script setup>
import { navigationStore, taakStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcDialog
		name="Contactmoment"
		size="large"
		label-id="contactMomentenForm"
		dialog-classes="ContactMomentenForm"
		:close-on-click-outside="false"
		@closing="closeModalFromButton()">
		<NcNoteCard v-if="success" type="success">
			<p>Contact moment succesvol opgeslagen</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success">
			<div class="headerContainer">
				<NcNoteCard type="info" class="noteCard">
					<template #default>
						<div v-if="klant">
							{{ getName(klant) }}
						</div>
						<div v-else>
							Geen klant geselecteerd
						</div>
						<div class="statusAndStartDateContainer">
							<div v-if="status">
								status: {{ status }}
							</div>
							<div v-if="startDate">
								startDate: {{ new Date(startDate).toLocaleDateString() }}
							</div>
						</div>
					</template>
				</NcNoteCard>
				<div v-if="!klant" class="buttonsContainer">
					<div>
						<NcButton
							:disabled="loading"
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
							type="primary"
							@click="openSearchKlantModal('organisatie')">
							<template #icon>
								<Plus :size="20" />
							</template>
							Organisatie zoeken
						</NcButton>
					</div>
				</div>

				<div v-if="klant" class="buttonsContainer">
					<div>
						<NcButton
							:disabled="loading"
							type="primary"
							@click="klant = null">
							<template #icon>
								<Minus :size="20" />
							</template>
							Klant ontkoppelen
						</NcButton>
					</div>
				</div>
			</div>

			<div v-if="!success" class="form-group">
				<NcTextArea v-model="notitie"
					label="Notitie"
					:disabled="loading"
					placeholder="Notitie" />
				<NcTextField
					:value.sync="titel"
					label="Titel"
					:disabled="loading" />
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
			<NcActions :disabled="loading || success" :primary="true" menu-name="Acties">
				<template #icon>
					<DotsHorizontal :size="20" />
				</template>
				<NcActionButton @click="openTaakForm">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
					Taak aanmaken
				</NcActionButton>
				<NcActionButton :disabled="true" @click="zaakStore.setZaakItem(); navigationStore.setModal('editZaak')">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
					Zaak starten
				</NcActionButton>
			</NcActions>
			<NcButton
				type="primary"
				:disabled="!klant || loading || success"
				:loading="loading"
				@click="addContactMoment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				Opslaan
			</NcButton>
		</template>
		<TakenForm v-if="taakFormOpen"
			:dashboard-widget="true"
			@close-modal="closeTaakForm"
			@save-success="closeTaakForm" />
	</NcDialog>
</template>

<script>
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcButton, NcActions, NcLoadingIcon, NcDialog, NcTextArea, NcNoteCard, NcListItem, NcActionButton, NcEmptyContent, NcTextField } from '@nextcloud/vue'

// Forms
import SearchKlantModal from '../../modals/klanten/SearchKlantModal.vue'
import TakenForm from '../../modals/taken/WidgetTaakForm.vue'

// Icons
import Plus from 'vue-material-design-icons/Plus.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Minus from 'vue-material-design-icons/Minus.vue'

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
		NcTextField,
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
	},
	data() {
		return {
			success: false,
			loading: false,
			error: false,
			titel: '',
			notitie: '',
			status: null,
			startDate: null,
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
		}
	},
	mounted() {
		if (this.contactMomentId) {
			this.fetchContactMomentData(this.contactMomentId)
		}
	},
	methods: {
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

		// Contact moment functions
		addContactMoment() {
			this.loading = true

			fetch(
				'/index.php/apps/zaakafhandelapp/api/contactmomenten',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						titel: this.titel,
						notitie: this.notitie,
						klant: this.klant?.id ?? '',
						zaak: this.selectedZaak ?? '',
						taak: this.selectedTaak ?? '',
						product: this.selectedProduct ?? '',
						status: this.status === 'gesloten' ? 'gesloten' : 'open',
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

		openTaakForm() {
			this.taakFormOpen = true
			taakStore.setTaakItem(null)
		},

		closeTaakForm() {
			this.taakFormOpen = false
		},

		fetchContactMomentData(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/contactmomenten/${id}`)
				.then(response => response.json())
				.then(data => {
					this.titel = data.titel
					this.notitie = data.notitie
					this.status = data.status
					this.startDate = data.startDate
					this.fetchKlantData(data.klant)
					this.selectedZaak = data.zaak
					this.selectedTaak = data.taak
					this.selectedProduct = data.product
				})
		},

		fetchKlantData(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}`)
				.then(response => response.json())
				.then(data => {
					this.klant = data
				})
				.catch(error => {
					console.error('Error fetching klant:', error)
					throw error // if this one fails, fetching the rest is pointless
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/zaken`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.zaken = data.results
					}
				})
				.catch(error => {
					console.error('Error fetching klant zaken:', error)
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/taken`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.taken = data.results
					}
				})
				.catch(error => {
					console.error('Error fetching klant taken:', error)
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/berichten`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.berichten = data.results
					}
				})
				.catch(error => {
					console.error('Error fetching klant berichten:', error)
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/audit_trail`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data)) {
						this.auditTrails = data
					}
				})
				.catch(error => {
					console.error('Error fetching klant audit trail:', error)
				})
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

.statusAndStartDateContainer {
	display: flex;
	gap: var(--zaa-margin-10);
}
</style>
