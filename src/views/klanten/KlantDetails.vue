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
							asdads
						</BTab>
						<BTab title="Taken">
							asda
						</BTab>
						<BTab title="Berichten">
							asdsa
						</BTab>
						<BTab title="Contact Momeenten">
							asdsa
						</BTab>
						<BTab title="Audit trail">
							asdsa
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
import { NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'KlantDetails',
	components: {
		NcActions,
		NcActionButton,
		BTabs,
		BTab,
		// Icons
		DotsHorizontal,
		Pencil,
		ChatOutline,
		CalendarMonthOutline,
		BriefcaseAccountOutline,
		TrashCanOutline,
	},
	data() {
		return {
			zaken: [],
			taken: [],
			berichten: [],
			contactMomenten: [],
			auditTrail: [],
		}
	},
	mounted() {
		this.fetchKlantData(klantStore.klantItem.id);
	},
	methods: {
		async fetchKlantData(id) {
			try {
				const [zaken, taken, berichten, contactMomenten, auditTrail] = await Promise.all([
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/zaken`).then(res => res.json()),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/taken`).then(res => res.json()),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/berichten`).then(res => res.json()),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/contactmomenten`).then(res => res.json()),
					fetch(`/index.php/apps/zaakafhandelapp/api/klanten/${id}/audit_trail`).then(res => res.json()),
				])
				this.zaken = zaken
				this.taken = taken
				this.berichten = berichten
				this.contactMomenten = contactMomenten
				this.auditTrail = auditTrail
			} catch (error) {
				console.error('Error fetching klant data:', error)
			}
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
