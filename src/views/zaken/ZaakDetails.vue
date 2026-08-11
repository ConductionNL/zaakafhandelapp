<!--
 ORPHANED LEGACY VIEW — this file is mounted by nothing.
 The manifest-v2 migration replaced it with the declarative "ZaakDetail" page
 (/zaken/:id), which the library renderer draws through CnPageRenderer ->
 CnDetailPage. Verified: no module under src/ imports it, so webpack never
 bundles it and no browser can reach it.
 @visual exclude Dead file: superseded by the manifest-v2 "ZaakDetail" page (/zaken/:id); imported by no module in src/, so it is in no bundle and no e2e can mount it. The live page is covered by tests/e2e/spec-coverage/ui-detail-views.spec.ts. Correct fix is deletion.
-->
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
						<NcActionButton v-if="!isClosed && (isSuspended || zaaktypeAllowsOpschorting)"
							@click="navigationStore.setModal('suspendZaak')">
							<template #icon>
								<PauseCircleOutline :size="20" />
							</template>
							{{ isSuspended ? t('zaakafhandelapp', 'Resume case') : t('zaakafhandelapp', 'Suspend case') }}
						</NcActionButton>
						<NcActionButton v-if="!isClosed && !isSuspended && zaaktypeAllowsVerlenging && !alreadyExtended"
							@click="navigationStore.setModal('extendZaak')">
							<template #icon>
								<CalendarPlus :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Extend case') }}
						</NcActionButton>
					</NcActions>
				</div>

				<NcNoteCard v-if="isSuspended" type="warning" class="suspensionBanner">
					{{ t('zaakafhandelapp', 'This case is suspended.') }}
					<span v-if="zaakStore.zaakItem?.opschorting?.reden">
						{{ t('zaakafhandelapp', 'Reason:') }} {{ zaakStore.zaakItem.opschorting.reden }}
					</span>
					<span v-if="suspensionStart">
						— {{ t('zaakafhandelapp', 'since') }} {{ suspensionStart }}
					</span>
				</NcNoteCard>

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
							<NcButton title="bekijken" variant="tertiary-no-background" @click="goToZaakType(zaakType)">
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
						<h4>{{ t('zaakafhandelapp', 'Planned end date:') }}</h4>
						<p>{{ zaakStore.zaakItem?.einddatumGepland || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Statutory deadline:') }}</h4>
						<p>{{ zaakStore.zaakItem?.uiterlijkeEinddatumAfdoening || '-' }}</p>
					</div>
					<div>
						<h4>Toelichting:</h4>
						<p>
							{{ zaakStore.zaakItem?.toelichting }}
						</p>
					</div>
				</div>
				<div class="tabContainer">
					<CnTabs content-class="mt-3" justified :aria-label="t('zaakafhandelapp', 'Case details')">
						<!-- TODO: Fix tabs -->
						<CnTab title="Eigenschappen" active>
							<ZaakEigenschappen :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Documenten">
							<ZaakDocumenten :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Resultaten">
							<ZaakResultaten :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Rollen">
							<ZaakRollen :zaak-url="zaakStore.zaakItem?.url" />
						</CnTab>
						<CnTab title="Taken">
							<ZaakTaken :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Besluiten">
							<ZaakBesluiten :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Berichten">
							<ZaakBerichten :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Zaken">
							<ZakenZaken :zaak-id="zaakStore.zaakItem?.id" />
						</CnTab>
						<CnTab title="Synchronisaties">
							Todo: Koppelings info met DSO
						</CnTab>
						<CnTab title="Audit trail">
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
						</CnTab>
					</CnTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// Components
import { CnTabs, CnTab } from '@conduction/nextcloud-vue'
import { NcActions, NcActionButton, NcButton, NcListItem, NcEmptyContent, NcLoadingIcon, NcNoteCard } from '@nextcloud/vue'

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
import PauseCircleOutline from 'vue-material-design-icons/PauseCircleOutline.vue'

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
		NcListItem,
		NcEmptyContent,
		NcNoteCard,
		CnTabs,
		CnTab,
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
		Eye,
		TimelineQuestionOutline,
		OpenInApp,
		FileChartCheckOutline,
		BriefcaseAccountOutline,
		MessagePlus,
		PauseCircleOutline,
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
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */
		zaakType() {
			return zaakTypeStore.zaakTypeList.find((zaakType) => zaakType.id === zaakStore.zaakItem.zaaktype || Symbol('no zaaktype id'))
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		isSuspended() {
			return zaakStore.zaakItem?.opschorting?.indicatie === true
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		isClosed() {
			return !!zaakStore.zaakItem?.einddatum
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		alreadyExtended() {
			return !!zaakStore.zaakItem?.verlenging?.duur
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		zaaktypeAllowsOpschorting() {
			const v = this.zaakType?.opschortingEnAanhoudingMogelijk
			return v === true || v === 'true' || v === '1'
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		zaaktypeAllowsVerlenging() {
			const v = this.zaakType?.verlengingMogelijk
			return v === true || v === 'true' || v === '1'
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		suspensionStart() {
			const raw = zaakStore.zaakItem?.opschorting?._opschortingGestart
			return raw ? new Date(raw).toLocaleDateString() : ''
		},
	},
	watch: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */
		id(newId) {
			this.fetchData(newId)
		},
	},
	/**
	 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
	 */
	mounted() {
		this.fetchData(this.id)
		zaakTypeStore.refreshZaakTypenList()
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-001
		 */
		fetchData(id) {
			this.loading = true

			Promise.all([
				zaakStore.getZaak(id, { setItem: true }),
				this.fetchAuditTrails(id),
			]).finally(() => {
				this.loading = false
			})
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-001
		 */
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
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */
		goToZaakType(zaakType) {
			zaakTypeStore.setZaakTypeItem(zaakType)
			this.$router.push({ name: 'ZaaktypeDetail', params: { id: zaakType.id } })
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
