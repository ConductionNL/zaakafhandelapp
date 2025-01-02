<script setup>
import { navigationStore, zaakStore, zaakTypeStore, resultaatStore, besluitStore, documentStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<NcLoadingIcon v-if="!zaakStore.zaakItem && loading" :size="64" />
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div v-if="zaakStore.zaakItem">
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
						<NcActionButton @click="(documentStore.zaakId = zaakStore.zaakItem?.id); documentStore.setDocumentItem(null); navigationStore.setModal('documentForm')">
							<template #icon>
								<FileDocumentPlusOutline :size="20" />
							</template>
							Document toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addRolToZaak')">
							<template #icon>
								<AccountPlus :size="20" />
							</template>
							Rol toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addTaakToZaak')">
							<template #icon>
								<CalendarPlus :size="20" />
							</template>
							Taak toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addBerichtToZaak')">
							<template #icon>
								<MessagePlus :size="20" />
							</template>
							Bericht toevoegen
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('updateZaakStatus')">
							<template #icon>
								<VectorPolylineEdit :size="20" />
							</template>
							Status wijzigen
						</NcActionButton>
						<NcActionButton @click="(resultaatStore.zaakId = zaakStore.zaakItem?.id); resultaatStore.setResultaatItem(null); navigationStore.setModal('resultaatForm')">
							<template #icon>
								<FileChartCheckOutline :size="20" />
							</template>
							Resultaat toevoegen
						</NcActionButton>
						<NcActionButton @click="(besluitStore.zaakId = zaakStore.zaakItem?.id); besluitStore.setBesluitItem(null); navigationStore.setModal('besluitForm')">
							<template #icon>
								<BriefcaseAccountOutline :size="20" />
							</template>
							Besluit toevoegen
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
						<span v-if="zaakStore.zaakItem.zaaktype" class="zaakType">
							{{ zaakType?.identificatie }}
							<NcButton v-tooltip="'bekijken'" type="tertiary-no-background" @click="goToZaakType(zaakType)">
								<template #icon>
									<OpenInApp :size="20" />
								</template>
							</NcButton>
						</span>
						<span v-else>geen zaaktype gevonden</span>
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
						<!-- TODO: Fix tabs -->
						<BTab title="Eigenschappen" active>
							<ZaakEigenschappen :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Documenten">
							<ZaakDocumenten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Resultaten">
							<ZaakResultaten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Rollen">
							<ZaakRollen :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Taken">
							<ZaakTaken :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Besluiten">
							<ZaakBesluiten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Berichten">
							<ZaakBerichten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Zaken">
							<ZakenZaken :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab title="Synchronisaties">
							Todo: Koppelings info met DSO
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
										<NcActionButton @click="zaakStore.setAuditTrailItem(auditTrail); navigationStore.setModal('viewZaakAuditTrail')">
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
									Er is geen audit trail gevonden voor deze zaak.
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
import { NcActions, NcActionButton, NcButton, NcListItem, NcEmptyContent, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'
import CalendarPlus from 'vue-material-design-icons/CalendarPlus.vue'
import MessagePlus from 'vue-material-design-icons/MessagePlus.vue'
import FileDocumentPlusOutline from 'vue-material-design-icons/FileDocumentPlusOutline.vue'
import VectorPolylineEdit from 'vue-material-design-icons/VectorPolylineEdit.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import TimelineQuestionOutline from 'vue-material-design-icons/TimelineQuestionOutline.vue'
import OpenInApp from 'vue-material-design-icons/OpenInApp.vue'
import FileChartCheckOutline from 'vue-material-design-icons/FileChartCheckOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

// Views
import ZaakEigenschappen from '../eigenschappen/ZaakEigenschappen.vue'
import ZaakBerichten from '../berichten/ZaakBerichten.vue'
import ZaakRollen from '../rollen/ZaakRollen.vue'
import ZaakTaken from '../taken/ZaakTaken.vue'
import ZaakBesluiten from '../besluiten/ZaakBesluiten.vue'
import ZaakDocumenten from '../documenten/ZaakDocumenten.vue'
import ZakenZaken from '../zaken/ZakenZaken.vue'
import ZaakResultaten from '../resultaten/ZaakResultaten.vue'

export default {
	name: 'ZaakDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		NcButton,
		BTabs,
		BTab,
		NcLoadingIcon,
		// Views
		ZaakEigenschappen,
		ZaakRollen,
		ZaakTaken,
		ZaakBerichten,
		ZaakBesluiten,
		ZaakDocumenten,
		ZakenZaken,
		ZaakResultaten,
		// Icons
		DotsHorizontal,
		Pencil,
		AccountPlus,
		CalendarPlus,
		FileDocumentPlusOutline,
		VectorPolylineEdit,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			// state
			loading: true,
			// data
			auditTrails: [],
			zaak: [],
		}
	},
	computed: {
		zaakType() {
			return zaakTypeStore.zaakTypeList.find((zaakType) => zaakType.id === zaakStore.zaakItem.zaaktype || Symbol('no zaaktype id'))
		},
	},
	watch: {
		id(newId) {
			this.fetchData(newId)
		},
	},
	mounted() {
		this.fetchData(this.id)
		zaakTypeStore.refreshZaakTypenList()
	},
	methods: {
		fetchData(id) {
			this.loading = true

			Promise.all([
				zaakStore.getZaak(id, { setItem: true }),
				this.fetchAuditTrails(id),
			]).finally(() => {
				this.loading = false
			})
		},
		fetchAuditTrails(id) {

			fetch(`/index.php/apps/zaakafhandelapp/api/zaken/${id}/audit_trail`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data)) {
						this.auditTrails = data
					}
				})
				.finally(() => {
				})
		},
		goToZaakType(zaakType) {
			zaakTypeStore.setZaakTypeItem(zaakType)
			this.$router.push({ name: 'dynamic-view', params: { view: 'zaaktypen', id: zaakType.id } })
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

<style scoped>
.zaakType {
	display: flex;
	align-items: center;
}
</style>
