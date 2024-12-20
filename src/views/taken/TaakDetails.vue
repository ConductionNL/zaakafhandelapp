<script setup>
import { navigationStore, taakStore, klantStore, medewerkerStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ taakStore.taakItem.title }}
					</h1>
					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editTaak')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteTaak')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<div class="grid">
					<div class="gridContent">
						<div>
							<b>Sammenvatting:</b>
							<span>{{ taakStore.taakItem.onderwerp }}</span>
						</div>
						<div v-if="taakStore.taakItem.medewerker">
							<b>Medewerker:</b>
							<span v-if="medewerkerLoading || !medewerker">Loading...</span>
							<div v-if="!medewerkerLoading && medewerker" class="buttonLinkContainer">
								<span>{{ medewerker.displayname }}</span>
							</div>
						</div>
						<div v-if="taakStore.taakItem.klant">
							<b>Klant:</b>
							<span v-if="klantLoading">Loading...</span>
							<div v-if="!klantLoading" class="buttonLinkContainer">
								<span>{{ getKlantName(klant) }}</span>
								<NcActions>
									<NcActionLink :aria-label="`ga naar ${getKlantName(klant)}`"
										:name="getKlantName(klant)"
										@click="goToKlant()">
										<template #icon>
											<OpenInApp :size="20" />
										</template>
										{{ getKlantName(klant) }}
									</NcActionLink>
								</NcActions>
							</div>
						</div>
					</div>
				</div>

				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Audit trail" active>
							<div v-if="auditTrails.length">
								<NcListItem v-for="(auditTrail, key) in auditTrails"
									:key="key"
									:name="new Date(auditTrail.created).toLocaleString()"
									:bold="false"
									:details="auditTrail.action"
									:counter-number="Object.keys(auditTrail.changed).length"
									:force-display-actions="true">
									<template #icon>
										<TimelineQuestionOutline disable-menu
											:size="44" />
									</template>
									<template #subname>
										{{ auditTrail.userName }}
									</template>
									<template #actions>
										<NcActionButton @click="taakStore.setAuditTrailItem(auditTrail); navigationStore.setModal('viewTaakAuditTrail')">
											<template #icon>
												<Eye :size="20" />
											</template>
											View details
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<NcEmptyContent v-else icon="icon-history" title="Geen audit trail gevonden">
								<template #description>
									Er is geen audit trail gevonden voor deze taak.
								</template>
							</NcEmptyContent>
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// Components
import { NcActions, NcActionButton, NcListItem, NcEmptyContent, NcActionLink } from '@nextcloud/vue'
import { BTabs, BTab } from 'bootstrap-vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import TimelineQuestionOutline from 'vue-material-design-icons/TimelineQuestionOutline.vue'
import OpenInApp from 'vue-material-design-icons/OpenInApp.vue'

export default {
	name: 'TaakDetails',
	components: {
		NcActionLink,
		// Icons
		Pencil,
		DotsHorizontal,
		TrashCanOutline,
		OpenInApp,
		TimelineQuestionOutline,
		Eye,
	},
	data() {
		return {
			currentActiveTaak: null,
			auditTrails: [],
			klant: null,
			medewerker: null,
			klantLoading: false,
			medewerkerLoading: false,
		}
	},
	mounted() {
		if (taakStore.taakItem?.id) {
			this.currentActiveTaak = taakStore.taakItem
			this.fetchAuditTrails(taakStore.taakItem.id)
			if (taakStore.taakItem.klant) this.fetchKlant(taakStore.taakItem.klant)
			if (taakStore.taakItem.medewerker) this.fetchMedewerker(taakStore.taakItem.medewerker)
		}
	},
	updated() {
		if (taakStore.taakItem?.id && JSON.stringify(this.currentActiveTaak) !== JSON.stringify(taakStore.taakItem)) {
			this.currentActiveTaak = taakStore.taakItem
			this.fetchAuditTrails(taakStore.taakItem.id)
			if (taakStore.taakItem.klant) this.fetchKlant(taakStore.taakItem.klant)
			if (taakStore.taakItem.medewerker) this.fetchMedewerker(taakStore.taakItem.medewerker)
		}
	},
	methods: {
		fetchAuditTrails(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/taken/${id}/audit_trail`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data)) {
						this.auditTrails = data
					}
				})
		},
		getKlantName(klant) {
			return klant?.type === 'persoon' ? `${klant?.voornaam} ${klant?.tussenvoegsel} ${klant?.achternaam}` : klant?.bedrijfsnaam
		},
		getMedewerkerName(medewerker) {
			return `${medewerker?.voornaam} ${medewerker?.tussenvoegsel} ${medewerker?.achternaam}`
		},
		goToKlant() {
			klantStore.setKlantItem(this.klant)
			navigationStore.setSelected('klanten')
		},
		goToMedewerker() {
			medewerkerStore.setMedewerkerItem(this.medewerker)
			navigationStore.setSelected('medewerkers')
		},
		fetchKlant(klant) {
			this.klantLoading = true

			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${klant}`, {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {
						this.klant = data
					})
					this.klantLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.klantLoading = false
				})

		},
		fetchMedewerker(medewerker) {
			this.medewerkerLoading = true

			const host = window.location.host

			fetch(`http://${host}/ocs/v1.php/cloud/users/details`, {
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'OCS-APIRequest': 'true',
				},
			}).then(response => response.json()).then(data => {

				this.medewerker = Object.values(data.ocs.data.users).find(user => user.email === medewerker)
			}).catch(err => {
				console.error(err)
			}).finally(() => {
				this.medewerkerLoading = false
			})

		},
	},
}
</script>

<style>
h4 {
  font-weight: bold
}

.h1 {
  display: block !important;
  font-size: 2em !important;
  margin-block-start: 0.67em !important;
  margin-block-end: 0.67em !important;
  margin-inline-start: 0px !important;
  margin-inline-end: 0px !important;
  font-weight: bold !important;
  unicode-bidi: isolate !important;
}

.grid {
  display: grid;
  grid-gap: 24px;
  grid-template-columns: 1fr 1fr;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  gap: 25px;
}

.buttonLinkContainer {
	display: flex;
	align-items: center;
}

</style>
