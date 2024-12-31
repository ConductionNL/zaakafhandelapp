<script setup>
import { navigationStore, berichtStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<NcLoadingIcon v-if="!berichtStore.berichtItem && loading" :size="64" />
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div v-if="berichtStore.berichtItem">
				<div class="head">
					<h1 class="h1">
						{{ berichtStore.berichtItem.onderwerp }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editBericht')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteBericht')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<div class="detailGrid">
					<div>
						<b>Berichttekst:</b>
						<p>{{ berichtStore.berichtItem.berichttekst }}</p>
					</div>
					<div>
						<b>Inhoud:</b>
						<p>{{ berichtStore.berichtItem.inhoud }}</p>
					</div>
					<div>
						<b>Soort gebruiker:</b>
						<span>{{ berichtStore.berichtItem.soortGebruiker }}</span>
					</div>
					<div>
						<b>Publicatiedatum:</b>
						<span>{{ berichtStore.berichtItem.publicatieDatum }}</span>
					</div>
					<div>
						<b>Aanmaak datum:</b>
						<span>{{ berichtStore.berichtItem.aanmaakDatum }}</span>
					</div>
					<div>
						<b>Bericht type:</b>
						<span>{{ berichtStore.berichtItem.berichtType }}</span>
					</div>
					<div>
						<b>Referentie:</b>
						<span>{{ berichtStore.berichtItem.referentie }}</span>
					</div>
					<div>
						<b>Bericht ID:</b>
						<span>{{ berichtStore.berichtItem.berichtID }}</span>
					</div>
					<div>
						<b>Batch ID:</b>
						<span>{{ berichtStore.berichtItem.batchID }}</span>
					</div>
					<div>
						<b>Gebruiker ID:</b>
						<span>{{ berichtStore.berichtItem.gebruikerID }}</span>
					</div>
					<div>
						<b>Volgorde:</b>
						<span>{{ berichtStore.berichtItem.volgorde }}</span>
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
										<NcActionButton @click="berichtStore.setAuditTrailItem(auditTrail); navigationStore.setModal('viewBerichtAuditTrail')">
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
									Er is geen audit trail gevonden voor deze bericht.
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
import { NcActions, NcActionButton, NcListItem, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'
import { BTabs, BTab } from 'bootstrap-vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import TimelineQuestionOutline from 'vue-material-design-icons/TimelineQuestionOutline.vue'

export default {
	name: 'BerichtDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		NcLoadingIcon,
		// Icons
		Pencil,
		DotsHorizontal,
		TrashCanOutline,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			currentActiveBericht: null,
			auditTrails: [],
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
		fetchData(id) {
			this.loading = true

			berichtStore.getBericht(id)
				.finally(() => {
					this.loading = false
				})

			this.fetchAuditTrails(id)
		},
		fetchAuditTrails(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/berichten/${id}/audit_trail`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data)) {
						this.auditTrails = data
					}
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

.tabPanel {
  padding: 20px 10px;
  min-height: 100%;
  max-height: 100%;
  height: 100%;
  overflow: auto;
}
</style>
