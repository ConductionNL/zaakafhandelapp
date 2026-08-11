<!--
 ORPHANED LEGACY VIEW — this file is mounted by nothing.
 The manifest-v2 migration replaced it with the declarative "Zaken" page
 (/zaken), which the library renderer draws through CnPageRenderer ->
 CnIndexPage. Verified: no module under src/ imports it, so webpack never
 bundles it and no browser can reach it.
 @visual exclude Dead file: superseded by the manifest-v2 "Zaken" page (/zaken); imported by no module in src/, so it is in no bundle and no e2e can mount it. The live page is covered by tests/e2e/spec-coverage/ui-case-views.spec.ts. Correct fix is deletion.
-->
<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					v-model="search"
					:show-trailing-button="search !== ''"
					:label="t('zaakafhandelapp', 'Search')"
					class="searchField"
					trailing-button-icon="close"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="zaakStore.refreshZakenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Refresh') }}
					</NcActionButton>
					<NcActionButton @click="zaakStore.setZaakItem(null); navigationStore.setModal('zaakForm')">
						<template #icon>
							<Plus :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Start case') }}
					</NcActionButton>
					<NcActionButton :model-value="sortByDeadline" @click="sortByDeadline = !sortByDeadline">
						<template #icon>
							<SortClockAscendingOutline :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Sort by deadline') }}
					</NcActionButton>
					<NcActionButton :model-value="overdueOnly" @click="overdueOnly = !overdueOnly">
						<template #icon>
							<AlertOutline :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Only overdue') }}
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="displayedZaken?.length">
				<NcListItem v-for="(zaak, i) in displayedZaken"
					:key="`${zaak}${i}`"
					:name="zaak?.identificatie"
					:force-display-actions="true"
					:active="$route.params?.id === zaak?.id"
					:details="zaak.uiterlijkeEinddatumAfdoening || ''"
					@click="openZaak(zaak)">
					<template #icon>
						<BriefcaseAccountOutline :class="zaakStore.zaakItem?.id === zaak?.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						<span>{{ zaakTypeStore.zaakTypeList.find(zaakType => zaakType.id === zaak.zaaktype)?.identificatie ?? zaak.zaaktype }}</span>
						<span v-if="urgencyOf(zaak)" :class="['urgencyBadge', `urgency-${urgencyOf(zaak)}`]">
							{{ t('zaakafhandelapp', urgencyLabelOf(zaak)) }}
						</span>
					</template>
					<template #actions>
						<NcActionButton @click="zaakStore.setZaakItem(zaak); navigationStore.setModal('zaakForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton disabled>
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Delete') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!zaakStore.zakenList.length && !loading">
			{{ t('zaakafhandelapp', 'No cases defined.') }}
		</div>

		<NcLoadingIcon v-if="!zaakStore.zakenList.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			:name="t('zaakafhandelapp', 'Loading cases')" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
import { deriveZaakUrgency, urgencyLabel } from '../../services/zaakUrgency.js'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import SortClockAscendingOutline from 'vue-material-design-icons/SortClockAscendingOutline.vue'
import AlertOutline from 'vue-material-design-icons/AlertOutline.vue'
export default {
	name: 'ZakenList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		BriefcaseAccountOutline,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
		SortClockAscendingOutline,
		AlertOutline,
	},
	data() {
		return {
			search: '',
			loading: false,
			zakenList: [],
			sortByDeadline: false,
			overdueOnly: false,
		}
	},
	computed: {
		/**
		 * The zaken list with the overdue filter and deadline sort applied
		 * (ui-case-views REQ-006).
		 *
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-006
		 */
		displayedZaken() {
			let list = [...(zaakStore.zakenList || [])]

			if (this.overdueOnly) {
				list = list.filter(zaak => this.urgencyOf(zaak) === 'verlopen')
			}

			if (this.sortByDeadline) {
				list.sort((a, b) => {
					const da = a.uiterlijkeEinddatumAfdoening ? new Date(a.uiterlijkeEinddatumAfdoening).getTime() : Infinity
					const db = b.uiterlijkeEinddatumAfdoening ? new Date(b.uiterlijkeEinddatumAfdoening).getTime() : Infinity
					return da - db
				})
			}

			return list
		},
	},
	/**
	 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
	 */
	mounted() {
		this.loading = true

		Promise.all([
			zaakStore.refreshZakenList(),
			zaakTypeStore.refreshZaakTypenList(),
		]).then(() => {
			this.loading = false
		})
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-003
		 */
		clearText() {
			this.search = ''
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-004
		 */
		openZaak(zaak) {
			zaakStore.setZaakItem(zaak)
			this.$router.push({ params: { id: zaak.id } })
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-006
		 */
		urgencyOf(zaak) {
			return deriveZaakUrgency(zaak)
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-006
		 */
		urgencyLabelOf(zaak) {
			return urgencyLabel(this.urgencyOf(zaak))
		},
	},
}
</script>

<style>
.searchField {
    padding-inline-start: 65px;
    padding-inline-end: 20px;
    margin-block-end: 6px;
}

.selectedZaakIcon>svg {
    fill: white;
}

.loadingIcon {
    margin-block-start: var(--zaa-margin-20);
}

.urgencyBadge {
    display: inline-block;
    margin-inline-start: 8px;
    padding: 1px 6px;
    border-radius: var(--border-radius);
    font-size: 0.85em;
    font-weight: bold;
}

.urgency-verlopen {
    background-color: var(--color-error);
    color: var(--color-primary-text);
}

.urgency-bijna-verlopen {
    background-color: var(--color-warning);
    color: var(--color-main-text);
}

.urgency-op-tijd {
    background-color: var(--color-success);
    color: var(--color-primary-text);
}
</style>
