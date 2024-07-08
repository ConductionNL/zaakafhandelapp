<script setup>
import { store } from '../../store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ zaak.omschrijving }}
					</h1>
					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="store.setModal('editZaak')">
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
						<NcActionButton>
							<template #icon>
								<AccountPlus :size="20" />
							</template>
							Rol toevoegen
						</NcActionButton>
						<NcActionButton>
							<template #icon>
								<CalendarPlus :size="20" />
							</template>
							Taak toevoegen
						</NcActionButton>
						<NcActionButton>
							<template #icon>
								<VectorPolylineEdit :size="20" />
							</template>
							Status wijzigen
						</NcActionButton>
					</NcActions>
				</div>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Eigenschappen" active>
							<ZaakEigenschappen :zaak-id="zaak.uuid" />
						</BTab>
						<BTab title="Documenten">
							<ZaakDocumenten :zaak-id="zaak.uuid" />
						</BTab>
						<BTab title="Rollen">
							<ZaakRollen :zaak-id="zaak.uuid" />
						</BTab>
						<BTab title="Taken">
							<ZaakTaken :zaak-id="zaak.uuid" />
						</BTab>
						<BTab title="Besluiten">
							<ZaakBesluiten :zaak-id="zaak.uuid" />
						</BTab>
						<BTab title="Berichten">
							<ZaakBerichten :zaak-id="zaak.uuid" />
						</BTab>
						<BTab title="Zaken">
							<ZakenZaken :zaak-id="zaak.uuid" />
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
import { BTabs, BTab } from 'bootstrap-vue'
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'
// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import CalendarPlus from 'vue-material-design-icons/CalendarPlus.vue'
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
		NcLoadingIcon,
		NcActions,
		NcActionButton,
		BTabs,
		BTab,
		DotsHorizontal,
		Pencil,
		AccountPlus,
		CalendarPlus,
		FileDocumentPlusOutline,
		VectorPolylineEdit,
		ZaakEigenschappen,
		ZaakRollen,
		ZaakTaken,
		ZaakBerichten,
		ZaakBesluiten,
		ZaakDocumenten,
		ZakenZaken,
	},
	props: {
		zaakId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			zaak: [],
			loading: false,
		}
	},
	watch: {
		zaakId: {
			handler(zaakId) {
				this.fetchData(zaakId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(store.zaakItem)
	},
	methods: {
		fetchData(zaakId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/zrc/zaken/' + zaakId,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.zaak = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
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
