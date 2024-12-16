<script setup>
import { taakStore, navigationStore, zaakStore, klantStore, contactMomentStore } from '../../store/store.js'
</script>

<template>
	<NcDialog name="Klant"
		size="normal"
		@closing="closeModalFromButton()">
		<h1 class="h1">
			{{ getKlantName(klant) }}
		</h1>
		<div class="detailGrid">
			<div class="gridContent gridFullWidth">
				<b>KVK nummer:</b>
				<p>{{ klant.kvkNummer || '-' }}</p>
			</div>

			<div class="gridContent">
				<b>Telefoonnummer:</b>
				<p>{{ klant.telefoonnummer || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Email adres:</b>
				<p>{{ klant.emailadres || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Adres:</b>
				<p>{{ `${klant.straatnaam} ${klant.huisnummer} ${klant.postcode} ${klant.plaats}` || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Functie:</b>
				<p>{{ klant.functie || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Bedrijfsnaam:</b>
				<p>{{ klant.bedrijfsnaam || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Website url:</b>
				<p>{{ klant.websiteUrl || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>url:</b>
				<p>{{ klant.url || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Bron organisatie:</b>
				<p>{{ klant.bronorganisatie || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Aanmaakkanaal:</b>
				<p>{{ klant.aanmaakkanaal || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Geverifieerd:</b>
				<p>{{ klant.geverifieerd || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Subject Identificatie:</b>
				<p>{{ klant.subjectIdentificatie || '-' }}</p>
			</div>
			<div class="gridContent">
				<b>Subject Type:</b>
				<p>{{ klant.subjectType || '-' }}</p>
			</div>
		</div>
		<div class="tabContainer">
			<BTabs content-class="mt-3" justified>
				<BTab title="Zaken">
					<div v-if="zaken?.length">
						<NcListItem v-for="(zaak, key) in zaken"
							:key="key"
							:name="zaak.identificatie"
							:bold="false"
							:details="zaak.omschrijving"
							:force-display-actions="true">
							<template #icon>
								<BriefcaseAccountOutline :size="44" />
							</template>
						</NcListItem>
					</div>
					<NcEmptyContent v-else icon="icon-folder" title="Geen zaken gevonden">
						<template #description>
							Er zijn geen zaken gevonden voor deze klant.
						</template>
					</NcEmptyContent>
				</BTab>
				<BTab title="Taken">
					<div v-if="taken?.length">
						<NcListItem v-for="(taak, key) in taken"
							:key="key"
							:name="taak.title"
							:bold="false"
							:details="taak.description"
							:force-display-actions="true">
							<template #icon>
								<CalendarMonthOutline :size="44" />
							</template>
						</NcListItem>
					</div>
					<NcEmptyContent v-else icon="icon-tasks" title="Geen taken gevonden">
						<template #description>
							Er zijn geen taken gevonden voor deze klant.
						</template>
					</NcEmptyContent>
				</BTab>
				<BTab title="Berichten">
					<div v-if="berichten?.length">
						<NcListItem v-for="(bericht, key) in berichten"
							:key="key"
							:name="bericht.title"
							:bold="false"
							:details="bericht.description"
							:force-display-actions="true">
							<template #icon>
								<ChatOutline :size="44" />
							</template>
							<template #actions>
								<NcActionButton @click="berichtStore.setBerichtItem(bericht); navigationStore.setModal('viewBericht')">
									<template #icon>
										<Eye :size="20" />
									</template>
									View details
								</NcActionButton>
							</template>
						</NcListItem>
					</div>
					<NcEmptyContent v-else icon="icon-mail" title="Geen berichten gevonden">
						<template #description>
							Er zijn geen berichten gevonden voor deze klant.
						</template>
					</NcEmptyContent>
				</BTab>
				<BTab title="Contact Momenten">
					<div v-if="filteredContactMomenten?.length">
						<NcListItem v-for="(contactMoment, key) in filteredContactMomenten"
							:key="key"
							:name="getName(klant)"
							:bold="false"
							:force-display-actions="true">
							<template #icon>
								<CardAccountPhoneOutline :size="44" />
							</template>
							<template #subname>
								{{ new Date(contactMoment.startDate).toLocaleString() }}
							</template>
						</NcListItem>
					</div>
					<NcEmptyContent v-else icon="icon-contacts" title="Geen contactmomenten gevonden">
						<template #description>
							Er zijn geen contactmomenten gevonden voor deze klant.
						</template>
					</NcEmptyContent>
				</BTab>
			</BTabs>
		</div>
		<template #actions>
			<NcActions :primary="true" menu-name="Acties">
				<template #icon>
					<DotsHorizontal :size="20" />
				</template>

				<NcActionButton @click="zaakStore.setZaakItem(); zaakModalOpen = true">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
					Zaak aanmaken
				</NcActionButton>

				<NcActionButton @click="taakStore.setTaakItem(); taakModalOpen = true">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
					Taak aanmaken
				</NcActionButton>

				<NcActionButton :disabled="true" @click="berichtStore.setBerichtItem(); berichtModalOpen = true">
					<template #icon>
						<ChatOutline :size="20" />
					</template>
					Contact moment aanmaken
				</NcActionButton>
			</NcActions>

			<NcButton @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				Sluiten
			</NcButton>
		</template>

		<WidgetZaakForm v-if="zaakModalOpen"
			:dashboard-widget="true"
			:selected-klant-from-widget="klant"
			@save-success="fetchZaakItems" />

		<EditTaakForm v-if="taakModalOpen"
			:dashboard-widget="true"
			:selected-klant-from-widget="klant"
			@save-success="fetchTaakItems" />
	</NcDialog>
</template>

<script>
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import {
	NcButton,
	NcDialog,
	NcEmptyContent,
	NcListItem,
	NcActions,
	NcActionButton,
} from '@nextcloud/vue'

import Cancel from 'vue-material-design-icons/Cancel.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import CardAccountPhoneOutline from 'vue-material-design-icons/CardAccountPhoneOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import EditTaak from '../../modals/taken/EditTaak.vue'
import WidgetZaakForm from '../../modals/zaken/WidgetZaakForm.vue'

export default {
	name: 'ViewKlant',
	components: {
		NcDialog,
		NcButton,
		NcActionButton,
		NcEmptyContent,
		NcListItem,
		BTabs,
		BTab,
		Cancel,
		BriefcaseAccountOutline,
		CalendarMonthOutline,
		ChatOutline,
		Eye,
		DotsHorizontal,
		EditTaakForm: EditTaak,
		WidgetZaakForm,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			required: false,
		},
		klantId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			klant: {},
			zaken: [],
			taken: [],
			berichten: [],
			auditTrails: [],
			contactMomenten: [],
			zaakModalOpen: false,
			taakModalOpen: false,
			berichtModalOpen: false,
			hasUpdated: false,
			currentActiveKlant: {},
		}
	},
	computed: {
		filteredContactMomenten() {
			return contactMomentStore.contactMomentenList.filter(contactMoment => contactMoment.klant === this.klant?.id)
		},
	},
	mounted() {
		contactMomentStore.refreshContactMomentenList()

		if (klantStore.widgetKlantId) {
			this.currentActiveKlant = klantStore.widgetKlantId
			this.fetchKlantData(klantStore.widgetKlantId)
		}
	},
	updated() {
		if (klantStore.widgetKlantId && this.currentActiveKlant !== klantStore.widgetKlantId) {
			this.currentActiveKlant = klantStore.widgetKlantId
			this.fetchKlantData(klantStore.widgetKlantId)
		}
	},
	methods: {

		fetchKlantData(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}`)
				.then(response => response.json())
				.then(data => {
					this.klant = data
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/zaken`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.zaken = data.results
					}
				})
				.catch(error => {
					console.error('Error fetching zaken:', error)
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/taken`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.taken = data.results
					}
				})
				.catch(error => {
					console.error('Error fetching taken:', error)
				})

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/berichten`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.berichten = data.results
					}
				})
				.catch(error => {
					console.error('Error fetching berichten:', error)
				})
		},

		fetchTaakItems() {
			this.taakModalOpen = false
			this.loading = true
			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${this.klantId}/taken`)
				.then(response => response.json())
				.then(data => {
					this.taken = data.results
				})
		},
		fetchZaakItems() {
			this.zaakModalOpen = false
			this.loading = true
			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${this.klantId}/zaken`)
				.then(response => response.json())
				.then(data => {
					this.zaken = data.results
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

		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		closeModal() {
			navigationStore.setModal(false)
			if (this.dashboardWidget) this.$emit('close-modal')
		},
		openLink(url, target) {
			window.open(url, target)
		},
		getKlantName(klant) {
			return klant?.type === 'persoon' ? `${klant?.voornaam} ${klant?.tussenvoegsel} ${klant?.achternaam}` : klant?.bedrijfsnaam
		},
	},
}
</script>
<style>
.detailContainer {
  margin-block-start: var(--zaa-margin-20);
  margin-inline-start: var(--zaa-margin-20);
  margin-inline-end: var(--zaa-margin-20);
}

.tabContainer > * ul > li {
  display: flex;
  flex: 1;
}

.tabContainer > * ul > li:hover {
  background-color: var(--color-background-hover);
}

.tabContainer > * ul > li > a {
  flex: 1;
  text-align: center;
}

.tabContainer > * ul > li > .active {
  background: transparent !important;
  color: var(--color-main-text) !important;
  border-bottom: var(--default-grid-baseline) solid var(--color-primary-element) !important;
}

.tabContainer > * ul[role="tablist"] {
  display: flex;
  margin: 10px 8px 0 8px;
  justify-content: space-between;
  border-bottom: 1px solid var(--color-border);
}

.tabContainer > * ul[role="tablist"] > * a[role="tab"] {
  padding-inline-start: 10px;
  padding-inline-end: 10px;
  padding-block-start: 10px;
  padding-block-end: 10px;
}

.tabContainer > * div[role="tabpanel"] {
  margin-block-start: var(--zaa-margin-10);
}

.tabPanel {
  padding: 20px 10px;
  min-height: 100%;
  max-height: 100%;
  height: 100%;
  overflow: auto;
}

.detailGrid {
    display: grid;
    grid-template-columns: 1fr 1fr;
}
</style>
