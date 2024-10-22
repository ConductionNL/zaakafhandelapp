<script setup>
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ zaakStore.zaakItem?.identificatie }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('zaakForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton>
							<template #icon>
								<FileDocumentPlusOutline :size="20" />
							</template>
							Document toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addRol')">
							<template #icon>
								<AccountPlus :size="20" />
							</template>
							Rol toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addTaak')">
							<template #icon>
								<CalendarPlus :size="20" />
							</template>
							Taak toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addBericht')">
							<template #icon>
								<MessagePlus :size="20" />
							</template>
							Bericht toevoegen
						</NcActionButton>
						<NcActionButton>
							<template #icon>
								<VectorPolylineEdit :size="20" />
							</template>
							Status wijzigen
						</NcActionButton>
					</NcActions>
				</div>

				<div class="detailGrid">
					<div>
						<h4>Omschrijving:</h4>
						<span>{{ zaakStore.zaakItem?.omschrijving }}</span>
					</div>
					<div>
						<h4>
							Zaaktype:
						</h4>
						<span>{{ zaakStore.zaakItem?.zaaktype }}</span>
					</div>
					<div>
						<div>
							<h4>Archiefstatus:</h4>
							<p>
								{{ zaakStore.zaakItem?.archiefstatus }}
							</p>
						</div>
						<h4>Registratiedatum:</h4>
						<span>{{ zaakStore.zaakItem?.registratiedatum }}</span>
					</div>
					<div>
						<h4>Bronorganisatie:</h4>
						<p>
							{{ zaakStore.zaakItem?.bronorganisatie }}
						</p>
					</div>
					<div>
						<h4>VerantwoordelijkeOrganisatie:</h4>
						<p>
							{{ zaakStore.zaakItem?.verantwoordelijkeOrganisatie }}
						</p>
					</div>
					<div>
						<h4>Startdatum:</h4>
						<p>
							{{ zaakStore.zaakItem?.startdatum }}
						</p>
					</div>
					<div>
						<h4>Toelichting:</h4>
						<p>
							{{ zaakStore.zaakItem?.toelichting }}
						</p>
					</div>
				</div>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Eigenschappen" active>
							<ZaakEigenschappen />
						</BTab>
						<BTab title="Documenten">
							<ZaakDocumenten />
						</BTab>
						<BTab title="Rollen">
							<ZaakRollen />
						</BTab>
						<BTab title="Taken">
							<ZaakTaken />
						</BTab>
						<BTab title="Besluiten">
							<ZaakBesluiten />
						</BTab>
						<BTab title="Berichten">
							<ZaakBerichten />
						</BTab>
						<BTab title="Zaken">
							<ZakenZaken />
						</BTab>
						<BTab title="Synchronisaties">
							Todo: Koppelings info met DSO
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			name="Zaak details aan het laden" />
	</div>
</template>

<script>
// Components
import { BTabs, BTab } from 'bootstrap-vue'
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import CalendarPlus from 'vue-material-design-icons/CalendarPlus.vue'
import MessagePlus from 'vue-material-design-icons/MessagePlus.vue'
import FileDocumentPlusOutline from 'vue-material-design-icons/FileDocumentPlusOutline.vue'
import VectorPolylineEdit from 'vue-material-design-icons/VectorPolylineEdit.vue'

// Views
import ZaakEigenschappen from '../eigenschappen/ZaakEigenschappen.vue'
import ZaakBerichten from '../berichten/ZaakBerichten.vue'
import ZaakRollen from '../rollen/ZaakRollen.vue'
import ZaakTaken from '../taken/ZaakTaken.vue'
import ZaakBesluiten from '../besluiten/ZaakBesluiten.vue'
import ZaakDocumenten from '../documenten/ZaakDocumenten.vue'
import ZakenZaken from '../zaken/ZakenZaken.vue'

export default {
	name: 'ZaakDetails',
	components: {
		// Components
		NcLoadingIcon,
		NcActions,
		NcActionButton,
		BTabs,
		BTab,
		// Views
		ZaakEigenschappen,
		ZaakRollen,
		ZaakTaken,
		ZaakBerichten,
		ZaakBesluiten,
		ZaakDocumenten,
		ZakenZaken,
		// Icons
		DotsHorizontal,
		Pencil,
		AccountPlus,
		CalendarPlus,
		FileDocumentPlusOutline,
		VectorPolylineEdit,

	},
	data() {
		return {
			zaak: [],
			loading: true,
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData() {
			this.loading = true

			// get current zaak once
			zaakStore.getZaak(zaakStore.zaakItem.uuid, { setZaakItem: true })
				.then(() => {
					this.loading = false
				})
		},
	},
}
</script>

<style>

h4 {
  font-weight: bold;
}

.head{
	display: flex;
	justify-content: space-between;
}

.button{
	max-height: 10px;
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

.dataContent {
  display: flex;
  flex-direction: column;
}

</style>
