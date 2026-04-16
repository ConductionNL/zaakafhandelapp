<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakStore, zaakTypeStore, resultaatStore, besluitStore, documentStore, rolStore } from '../../store/store.js'
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

					<NcActions :primary="true" :menu-name="t('zaakafhandelapp', 'Actions')">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('zaakForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton @click="(documentStore.zaakId = zaakStore.zaakItem?.id); documentStore.setDocumentItem(null); navigationStore.setModal('documentForm')">
							<template #icon>
								<FileDocumentPlusOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Add document') }}
						</NcActionButton>
						<NcActionButton @click="() => {
							rolStore.setRolItem(null);
							rolStore.setZaakId(zaakStore.zaakItem?.id);
							rolStore.extraData.redirect = false;
							navigationStore.setModal('rolForm')
						}">
							<template #icon>
								<AccountPlus :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Create role') }}
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addTaakToZaak')">
							<template #icon>
								<CalendarPlus :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Add task') }}
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('addBerichtToZaak')">
							<template #icon>
								<MessagePlus :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Add message') }}
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('updateZaakStatus')">
							<template #icon>
								<VectorPolylineEdit :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Change status') }}
						</NcActionButton>
						<NcActionButton @click="(resultaatStore.zaakId = zaakStore.zaakItem?.id); resultaatStore.setResultaatItem(null); navigationStore.setModal('resultaatForm')">
							<template #icon>
								<FileChartCheckOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Add result') }}
						</NcActionButton>
						<NcActionButton @click="(besluitStore.zaakId = zaakStore.zaakItem?.id); besluitStore.setBesluitItem(null); navigationStore.setModal('besluitForm')">
							<template #icon>
								<BriefcaseAccountOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Add decision') }}
						</NcActionButton>
					</NcActions>
				</div>

				<div class="detailGrid">
					<div>
						<h4>{{ t('zaakafhandelapp', 'Description:') }}</h4>
						<span>{{ zaakStore.zaakItem?.omschrijving }}</span>
					</div>
					<div>
						<h4>
							{{ t('zaakafhandelapp', 'Case type:') }}
						</h4>
						<span v-if="zaakStore.zaakItem.zaaktype" class="zaakType">
							{{ zaakType?.identificatie }}
							<NcButton v-tooltip="t('zaakafhandelapp', 'View')" type="tertiary-no-background" @click="goToZaakType(zaakType)">
								<template #icon>
									<OpenInApp :size="20" />
								</template>
							</NcButton>
						</span>
						<span v-else>{{ t('zaakafhandelapp', 'No case type') }}</span>
					</div>
					<div>
						<div>
							<h4>{{ t('zaakafhandelapp', 'Archive status:') }}</h4>
							<p>
								{{ zaakStore.zaakItem?.archiefstatus }}
							</p>
						</div>
						<h4>{{ t('zaakafhandelapp', 'Registration date:') }}</h4>
						<span>{{ zaakStore.zaakItem?.registratiedatum }}</span>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Source organisation:') }}</h4>
						<p>
							{{ zaakStore.zaakItem?.bronorganisatie }}
						</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Responsible organisation:') }}</h4>
						<p>
							{{ zaakStore.zaakItem?.verantwoordelijkeOrganisatie }}
						</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Start date:') }}</h4>
						<p>
							{{ zaakStore.zaakItem?.startdatum }}
						</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Explanation:') }}</h4>
						<p>
							{{ zaakStore.zaakItem?.toelichting }}
						</p>
					</div>
				</div>
				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<!-- TODO: Fix tabs -->
						<BTab :title="t('zaakafhandelapp', 'Properties')" active>
							<ZaakEigenschappen :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Documents')">
							<ZaakDocumenten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Results')">
							<ZaakResultaten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Roles')">
							<ZaakRollen :zaak-url="zaakStore.zaakItem?.url" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Tasks')">
							<ZaakTaken :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Decisions')">
							<ZaakBesluiten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Messages')">
							<ZaakBerichten :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Cases')">
							<ZakenZaken :zaak-id="zaakStore.zaakItem?.id" />
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Synchronizations')">
							{{ t('zaakafhandelapp', 'Todo: Connection info with DSO') }}
						</BTab>
						<BTab :title="t('zaakafhandelapp', 'Audit trail')">
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
											{{ t('zaakafhandelapp', 'View details') }}
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<NcEmptyContent v-else icon="icon-history" :title="t('zaakafhandelapp', 'No audit trail found')">
								<template #description>
									{{ t('zaakafhandelapp', 'No audit trail was found for this case.') }}
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
