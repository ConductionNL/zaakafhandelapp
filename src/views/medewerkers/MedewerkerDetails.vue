<script setup>
import { navigationStore, medewerkerStore, taakStore, berichtStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<NcLoadingIcon v-if="!medewerkerStore.medewerkerItem && loading" :size="64" />
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div v-if="medewerkerStore.medewerkerItem">
				<div class="head">
					<h1 class="h1">
						{{ getName(medewerkerStore.medewerkerItem) }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('editMedewerker')">
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
						<NcActionButton @click="navigationStore.setDialog('deleteMedewerker')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<span> {{ medewerkerStore.medewerkerItem.subject }} </span>

				<div class="gridContent">
					<div>
						<b>Email adres:</b>
						<p>{{ medewerkerStore.medewerkerItem.email }}</p>
					</div>
					<div>
						<b>Telefoonnummer:</b>
						<p>{{ medewerkerStore.medewerkerItem.telefoonnummer }}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// Components
import { NcActions, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'
import { countries } from '../../data/countries.js'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'MedewerkerDetails',
	components: {
		NcActions,
		NcActionButton,
		// Icons
		DotsHorizontal,
		Pencil,
		ChatOutline,
		CalendarMonthOutline,
		BriefcaseAccountOutline,
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
			zaken: [],
			taken: [],
			berichten: [],
			contactMomenten: [],
			auditTrails: [],
			loading: true,
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

			medewerkerStore.getMedewerker(id)
				.finally(() => {
					this.loading = false
				})
		},
		getName(medewerker) {
			return `${medewerker.voornaam} ${medewerker.tussenvoegsel} ${medewerker.achternaam}` ?? 'onbekend'
		},

		getLandName(landId) {
			return countries.find(country => country.code === landId)?.name ?? 'onbekend'
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
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
	margin-block-start: var(--zaa-margin-50);
	margin-block-end: var(--zaa-margin-50);
}

.gridContent {
	display: flex;
	flex-direction: column;
	gap: 2px !important;
}

.gridContent>h5 {
	margin-top: 12px !important;
}

.gridFullWidth {
	grid-column: 1 / -1;
}
</style>
