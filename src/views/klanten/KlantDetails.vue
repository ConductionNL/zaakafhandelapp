<script setup>
import { navigationStore, klantStore, taakStore, berichtStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ klantStore.klantItem.voornaam }} {{ klantStore.klantItem.voorvoegsel }} {{ klantStore.klantItem.achternaam }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="taakStore.setTaakItem(); navigationStore.setModal('editTaak')">
							<template #icon>
								<CalendarMonthOutline :size="20" />
							</template>
							Taak geven
						</NcActionButton>
						<NcActionButton @click="berichtStore.setBerichtItem(); navigationStore.setModal('editBericht')">
							<template #icon>
								<ChatOutline :size="20" />
							</template>
							Bericht versturen
						</NcActionButton>
						<NcActionButton @click="zaakStore.setZaakItem(); navigationStore.setModal('editZaak')">
							<template #icon>
								<BriefcaseAccountOutline :size="20" />
							</template>
							Zaak starten
						</NcActionButton>
						<NcActionButton @click="navigationStore.setDialog('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<span> {{ klantStore.klantItem.subject }} </span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>Klantnummer:</b>
						<p>{{ klantStore.klantItem.klantnummer }}</p>
					</div>

					<div class="gridContent">
						<b>Telefoonnummer:</b>
						<p>{{ klantStore.klantItem.telefoonnummer }}</p>
					</div>
					<div class="gridContent">
						<b>Email adres:</b>
						<p>{{ klantStore.klantItem.emailadres }}</p>
					</div>
					<div class="gridContent">
						<b>Adres:</b>
						<p>{{ klantStore.klantItem.adres }}</p>
					</div>
					<div class="gridContent">
						<b>Functie:</b>
						<p>{{ klantStore.klantItem.functie }}</p>
					</div>
					<div class="gridContent">
						<b>Bedrijfsnaam:</b>
						<p>{{ klantStore.klantItem.bedrijfsnaam }}</p>
					</div>
					<div class="gridContent">
						<b>Website url:</b>
						<p>{{ klantStore.klantItem.websiteUrl }}</p>
					</div>
					<div class="gridContent">
						<b>url:</b>
						<p>{{ klantStore.klantItem.url }}</p>
					</div>
					<div class="gridContent">
						<b>Bron organisatie:</b>
						<p>{{ klantStore.klantItem.bronorganisatie }}</p>
					</div>
					<div class="gridContent">
						<b>Aanmaakkanaal:</b>
						<p>{{ klantStore.klantItem.aanmaakkanaal }}</p>
					</div>
					<div class="gridContent">
						<b>Geverifieerd:</b>
						<p>{{ klantStore.klantItem.geverifieerd }}</p>
					</div>
					<div class="gridContent">
						<b>Subject Identificatie:</b>
						<p>{{ klantStore.klantItem.subjectIdentificatie }}</p>
					</div>
					<div class="gridContent">
						<b>Subject Type:</b>
						<p>{{ klantStore.klantItem.subjectType }}</p>
					</div>
				</div>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Zaken">
							<div v-if="zaken.length">
								<NcListItem v-for="(zaak, key) in zaken"
									:key="key"
									:name="zaak.title"
									:bold="false"
									:details="zaak.description"
									:force-display-actions="true">
									<template #icon>
										<BriefcaseAccountOutline :size="44" />
									</template>
									<template #actions>
										<NcActionButton @click="zaakStore.setZaakItem(zaak); navigationStore.setModal('viewZaak')">
											<template #icon>
												<Eye :size="20" />
											</template>
											View details
										</NcActionButton>
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
							<div v-if="taken.length">
								<NcListItem v-for="(taak, key) in taken"
									:key="key"
									:name="taak.title"
									:bold="false"
									:details="taak.description"
									:force-display-actions="true">
									<template #icon>
										<CalendarMonthOutline :size="44" />
									</template>
									<template #actions>
										<NcActionButton @click="taakStore.setTaakItem(taak); navigationStore.setModal('viewTaak')">
											<template #icon>
												<Eye :size="20" />
											</template>
											View details
										</NcActionButton>
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
							<div v-if="berichten.length">
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
							<div v-if="contactMomenten.length">
								<NcListItem v-for="(contactMoment, key) in contactMomenten"
									:key="key"
									:name="contactMoment.title"
									:bold="false"
									:details="contactMoment.description"
									:force-display-actions="true">
									<template #icon>
										<AccountOutline :size="44" />
									</template>
									<template #actions>
										<NcActionButton @click="contactMomentStore.setContactMomentItem(contactMoment); navigationStore.setModal('viewContactMoment')">
											<template #icon>
												<Eye :size="20" />
											</template>
											View details
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<NcEmptyContent v-else icon="icon-contacts" title="Geen contactmomenten gevonden">
								<template #description>
									Er zijn geen contactmomenten gevonden voor deze klant.
								</template>
							</NcEmptyContent>
						</BTab>
						<BTab title="Audit trail">
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
										<NcActionButton @click="objectStore.setAuditTrailItem(auditTrail); navigationStore.setModal('viewObjectAuditTrail')">
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
									Er is geen audit trail gevonden voor deze klant.
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
import { BTabs, BTab } from 'bootstrap-vue'
import { NcActions, NcActionButton, NcEmptyContent, NcListItem } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import TimelineQuestionOutline from 'vue-material-design-icons/TimelineQuestionOutline.vue'

export default {
	name: 'KlantDetails',
	components: {
		NcActions,
		NcActionButton,
		NcEmptyContent,
		BTabs,
		BTab,
		NcListItem,
		// Icons
		DotsHorizontal,
		Pencil,
		ChatOutline,
		CalendarMonthOutline,
		BriefcaseAccountOutline,
		TrashCanOutline,
		Eye,
		TimelineQuestionOutline,
	},
	data() {
		return {
			zaken: [],
			taken: [],
			berichten: [],
			contactMomenten: [],
			auditTrails: [],
		}
	},
	mounted() {
		this.fetchKlantData(klantStore.klantItem.id);
	},
	methods: {
		fetchKlantData(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/zaken`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.zaken = data.results;
					}
					console.log(this.zaken);
					return fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/taken`);
				})
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.taken = data.results;
					}
					console.log(this.taken);
					return fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/berichten`);
				})
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data.results)) {
						this.berichten = data.results;
					}
					console.log(this.berichten);
					return fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/audit_trail`);
				})
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data)) {
						this.auditTrails = data;
					}
					console.log(this.auditTrails);
				})
				.catch(error => {
					console.error('Error fetching klant data:', error);
				});
		},
	},
}
</script>

<style>
.detailContainer {
    padding: 0.5rem;
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
  grid-gap: 1rem 24px !important;
  grid-template-columns: repeat( auto-fit, minmax(300px, 1fr) ) !important;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  flex-direction: column;
  gap: 2px !important;
}
.gridContent > h5 {
    margin-top: 12px !important;
}

.gridFullWidth {
    grid-column: 1 / -1;
}

</style>
