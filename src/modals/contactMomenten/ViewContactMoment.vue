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
		<div>
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
						<BTab
							:title="`Contactmomenten ${klant ? (klantContactmomenten.length ? `(${klantContactmomenten.length})` : '(0)') : ''}`">
							<div v-if="klantContactmomenten.length">
								<NcListItem v-for="(klantContactmoment, key) in klantContactmomenten"
									:key="key"
									:name="getName(klant)"
									:bold="false"
									:details="new Date(klantContactmoment.startDate).toLocaleString()"
									:disabled="loading"
									:active="selectedKlantContactMoment === klantContactmoment.id"
									:force-display-actions="true">
									<template #icon>
										<BriefcaseAccountOutline :size="44" />
									</template>
								</NcListItem>
							</div>
							<NcEmptyContent v-else icon="icon-folder" title="Geen contactmomenten gevonden">
								<template #description>
									Er zijn geen contactmomenten gevonden voor deze klant.
								</template>
							</NcEmptyContent>
						</BTab>
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
									:force-display-actions="true">
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
									:force-display-actions="true">
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
									:force-display-actions="true">
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
			</div>
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
			<NcActions :disabled="loading || success || fetchLoading"
				:primary="true"
				:force-name="true"
				menu-name="Acties">
				<template #icon>
					<DotsHorizontal :size="20" />
				</template>
				<NcActionButton v-if="!isView" @click="openTaakForm('medewerker')">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
					Medewerker taak aanmaken
				</NcActionButton>
				<NcActionButton v-if="!isView" @click="openTaakForm('klant')">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
					Klant taak aanmaken
				</NcActionButton>
				<NcActionButton v-if="!isView" :disabled="!contactMomenten[selectedContactMoment].klant?.id" @click="openZaakForm()">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
					Zaak starten
				</NcActionButton>
			</NcActions>
			<NcButton
				v-if="!isView"
				type="primary"
				:disabled="!contactMomenten[selectedContactMoment].klant || loading || success || fetchLoading"
				:loading="loading"
				@click="addContactMoment(selectedContactMoment)">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-else :size="20" />
				</template>
				{{ isEdit ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcButton, NcActions, NcLoadingIcon, NcDialog, NcTextArea, NcNoteCard, NcListItem, NcActionButton, NcEmptyContent } from '@nextcloud/vue'
import _ from 'lodash'

import getValidISOstring from '../../services/getValidISOstring.js'
// Forms

// Entities
import { ContactMoment } from '../../entities/index.js'

// Icons
import Plus from 'vue-material-design-icons/Plus.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Minus from 'vue-material-design-icons/Minus.vue'

export default {
	name: 'ViewContactMoment',
	components: {
		NcDialog,
		NcTextArea,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		NcListItem,
		NcActionButton,
		NcEmptyContent,
		BTabs,
		BTab,
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
		 * The ID of the klant to fetch data for
		 */
		klantId: {
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
			isEdit: null,
			fetchLoading: false, // used as the loading state when editing
			success: false,
			loading: false,
			error: false,
			contactMoment: {
				titel: '',
				notitie: '',
				status: 'open',
				startDate: null,
				klant: null,
				selectedZaak: null,
				selectedTaak: null,
				selectedProduct: null,
			},
			klantenLoading: false,
			zaken: [],
			taken: [],
			berichten: [],
			klantContactmomenten: [],
			contactMomenten: {
				1: {
					notitie: '',
					status: 'open',
					startDate: null,
					klant: null,
					selectedZaak: null,
					selectedTaak: null,
					selectedProduct: null,
					selectedKlantContactMoment: null,
					zaken: [],
					taken: [],
					berichten: [],
					klantContactmomenten: [],
				},
			},
			selectedContactMoment: 1,
			auditTrails: [],
			klant: null,
			searchKlantModalOpen: false,
			selectedZaak: null,
			selectedTaak: null,
			selectedProduct: null,
			selectedKlantContactMoment: null,
			startingType: 'all',
			taakFormOpen: false,
			taakClientType: 'both',
			zaakFormOpen: false,

			tabs: [1],
			tabCounter: 1,
		}
	},
	mounted() {
		const contactMomentId = this.contactMomentId ?? contactMomentStore.contactMomentItem?.id ?? null
		this.isEdit = !!contactMomentId

		if (this.isEdit) {
			this.fetchData(contactMomentId)
		} else if (this.klantId) {
			// if it is not an edit modal but a klantId is provided, fetch the klant data
			this.fetchKlantData(this.klantId)
		}
	},
	methods: {

		closeTab(x) {
			for (let i = 0; i < this.tabs.length; i++) {
				if (this.tabs[i] === x) {
					this.tabs.splice(i, 1)
				}
			}
		},
		newTab() {
			const index = this.tabCounter + 1
			this.contactMomenten = {
				...this.contactMomenten,
				[index]: {
					notitie: '',
					status: 'open',
					startDate: null,
					klant: null,
					selectedZaak: null,
					selectedTaak: null,
					selectedProduct: null,
					selectedKlantContactMoment: null,
				},
			}
			this.tabs.push(index)
			this.tabCounter = this.tabCounter + 1
		},

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
			this.selectedKlantContactMoment = data.contactmoment
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
		addContactMoment(i) {

			this.selectedContactMoment = i
			this.contactMoment = this.contactMomenten[i]

			this.loading = true

			const endpoint = this.contactMomentId ? `contactmomenten/${this.contactMomentId}` : 'contactmomenten'
			const method = this.contactMomentId ? 'PUT' : 'POST'

			const contactMomentCopy = _.cloneDeep(this.contactMoment)

			delete contactMomentCopy.taken
			delete contactMomentCopy.zaken
			delete contactMomentCopy.berichten
			delete contactMomentCopy.klantContactmomenten
			delete contactMomentCopy.auditTrails

			fetch(
				`/index.php/apps/zaakafhandelapp/api/${endpoint}`,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						...contactMomentCopy,
						notitie: contactMomentCopy.notitie,
						klant: contactMomentCopy.klant?.id ?? '',
						zaak: contactMomentCopy.selectedZaak ?? '',
						taak: contactMomentCopy.selectedTaak ?? '',
						product: contactMomentCopy.selectedProduct ?? '',
						status: contactMomentCopy.status === 'gesloten' ? 'gesloten' : 'open',
						startDate: contactMomentCopy.startDate ?? new Date().toISOString(),
					}),
				},
			)
				.then((response) => {
					if (this.isView) {
						response.json().then(data => {
							this.contactMoment = data
						})

						if (this.dashboardWidget === true) {
							this.$emit('save-success')
						}

						this.loading = false
						return
					}

					this.succesMessage = true
					// Get the modal to self close
					this.success = true
					this.loading = false

					if (!this.dashboardWidget) {
						setTimeout(() => {
							this.closeModal()
						}, 2000)
					}

					if (this.dashboardWidget === true) {
						setTimeout(() => {
							this.closeTab(this.selectedContactMoment)
							this.selectedContactMoment = 1
							this.success = false
							this.succesMessage = false
						}, 2000)
						if (this.tabs.length === 1) {
							this.$emit('save-success')
							setTimeout(() => {
								this.closeModal()
							}, 2000)
						}
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
			this.fetchKlantData(this.contactMomenten[this.selectedContactMoment].klant.id)
		},

		removeKlant(i) {
			this.contactMomenten[i].klant = null
			this.contactMomenten[i].taken = null
			this.contactMomenten[i].zaken = null
			this.contactMomenten[i].berichten = null
			this.contactMomenten[i].klantContactmomenten = null
			this.contactMomenten[i].auditTrails = null
		},

		// zaak functions
		openZaakForm() {
			zaakStore.setZaakItem(null)
			this.zaakFormOpen = true
		},

		zaakFormSaveSuccess() {
			this.zaakFormOpen = false
			this.fetchKlantData(this.contactMomenten[this.selectedContactMoment].klant.id)
		},

		async closeContactMoment(id) {
			const { data } = await contactMomentStore.getContactMoment(id)

			if (data?.status === 'gesloten') {
				console.info('Contactmoment is already closed')
				return
			}
			const newContactMoment = new ContactMoment({
				...data,
				status: 'gesloten',
			})

			contactMomentStore.saveContactMoment(newContactMoment)
				.then(({ response }) => {
					if (response.ok) {
						this.closeModal()
						this.$emit('save-success')
					}
				})
		},

		async fetchKlantData(id) {
			try {
				const klantResponse = await fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}`)
				// ifselectedContactMoment
				if (this.isView) {
					this.klant = await klantResponse.json()
				} else {
					this.contactMomenten[this.selectedContactMoment].klant = await klantResponse.json()
				}

				// fetch all data in parallel
				const [zakenRes, takenRes, berichtenRes, auditTrailRes, contactMomentenRes] = await Promise.all([
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/zaken`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/taken`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/berichten`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/audit_trail`),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/contactmomenten`),
				])

				// parse all data
				const [zakenData, takenData, berichtenData, auditTrailData, contactMomentenData] = await Promise.all([
					zakenRes.json(),
					takenRes.json(),
					berichtenRes.json(),
					auditTrailRes.json(),
					contactMomentenRes.json(),
				])

				// set data
				if (Array.isArray(zakenData.results)) {
					if (this.isView) {
						this.zaken = zakenData.results
					} else {
						this.contactMomenten[this.selectedContactMoment].zaken = zakenData.results
					}
				}
				if (Array.isArray(takenData.results)) {
					if (this.isView) {
						this.taken = takenData.results
					} else {
						this.contactMomenten[this.selectedContactMoment].taken = takenData.results
					}
				}
				if (Array.isArray(berichtenData.results)) {
					if (this.isView) {
						this.berichten = berichtenData.results
					} else {
						this.contactMomenten[this.selectedContactMoment].berichten = berichtenData.results
					}
				}
				if (Array.isArray(auditTrailData)) {
					if (this.isView) {
						this.auditTrails = auditTrailData
					} else {
						this.contactMomenten[this.selectedContactMoment].auditTrails = auditTrailData
					}
				}
				if (Array.isArray(contactMomentenData.results)) {
					if (this.isView) {
						this.klantContactmomenten = contactMomentenData.results
					} else {
						this.contactMomenten[this.selectedContactMoment].klantContactmomenten = contactMomentenData.results
					}
				}

			} catch (error) {
				console.error('Error in fetchKlantData:', error)
				throw error
			}
		},

		getName(klant) {
			if (!klant) return

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
		setSelectedZaak(id, zaak) {
			if (this.contactMomenten[id].selectedZaak === zaak) {
				this.contactMomenten[id].selectedZaak = null
			} else { this.contactMomenten[id].selectedZaak = zaak }
		},

		setSelectedTaak(id, taak) {
			if (this.contactMomenten[id].selectedTaak === taak) {
				this.contactMomenten[id].selectedTaak = null
			} else { this.contactMomenten[id].selectedTaak = taak }
		},

		setSelectedProduct(id, product) {
			if (this.contactMomenten[id].selectedProduct === product) {
				this.contactMomenten[id].selectedProduct = null
			} else { this.contactMomenten[id].selectedProduct = product }
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

.newTabButton > a {
	display: flex;
	justify-content: center;
	align-items: center;
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
