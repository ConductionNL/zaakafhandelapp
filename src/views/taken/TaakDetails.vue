<script setup>
import { navigationStore, taakStore, klantStore, medewerkerStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<NcLoadingIcon v-if="!taakStore.taakItem && loading" :size="64" />
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div v-if="taakStore.taakItem">
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
							<span v-if="medewerkerLoading">Loading...</span>
							<div v-if="!medewerkerLoading" class="buttonLinkContainer">
								<span>{{ getMedewerkerName(medewerker) }}</span>
								<NcActions>
									<NcActionLink :aria-label="`ga naar ${getMedewerkerName(medewerker)}`"
										:name="getMedewerkerName(medewerker)"
										@click="goToMedewerker()">
										<template #icon>
											<OpenInApp :size="20" />
										</template>
										{{ getMedewerkerName(medewerker) }}
									</NcActionLink>
								</NcActions>
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
import { NcActions, NcActionButton, NcListItem, NcEmptyContent, NcActionLink, NcLoadingIcon } from '@nextcloud/vue'
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
		NcLoadingIcon,
		// Icons
		Pencil,
		DotsHorizontal,
		TrashCanOutline,
		OpenInApp,
		TimelineQuestionOutline,
		Eye,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			auditTrails: [],
			klant: null,
			medewerker: null,
			klantLoading: false,
			medewerkerLoading: false,
			loading: false,
		}
	},
	watch: {
		id(newId) {
			this.fetchData(newId)
		},
	},
	mounted() {
		this.fetchData(this.id)
	},
	methods: {
		async fetchData(id) {
			this.loading = true

			const [{ data: taakData }] = await Promise.all([
				taakStore.getTaak(id, { setItem: true }),
				this.fetchAuditTrails(id),
			])

			this.loading = false

			Promise.all([
				...(taakData.klant ? [this.fetchKlant(taakData.klant)] : []),
				...(taakData.medewerker ? [this.fetchMedewerker(taakData.medewerker)] : []),
			])
		},
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
			this.$router.push({ name: 'dynamic-view', params: { view: 'klanten', id: this.klant.id } })
		},
		goToMedewerker() {
			medewerkerStore.setMedewerkerItem(this.medewerker)
			this.$router.push({ name: 'dynamic-view', params: { view: 'medewerkers', id: this.medewerker.id } })
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

			fetch(`/index.php/apps/zaakafhandelapp/api/medewerkers/${medewerker}`, {
				method: 'GET',
			})
				.then((response) => {
					response.json().then((data) => {
						this.medewerker = data
					})
					this.medewerkerLoading = false
				})
				.catch((err) => {
					console.error(err)
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
