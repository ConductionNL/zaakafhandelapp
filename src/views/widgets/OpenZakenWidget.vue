<script setup>
import { zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="openZakenContainer">
		<p v-if="overdueCount > 0" class="overdueHeader">
			{{ overdueHeaderText }}
		</p>
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent :name="t('zaakafhandelapp', 'No open cases')">
						<template #icon>
							<Folder />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>
		<NcButton type="primary" @click="search">
			<template #icon>
				<OpenInApp :size="20" />
			</template>
			Zaken bekijken
		</NcButton>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import { getTheme } from '../../services/getTheme.js'
import { deriveZaakUrgency, urgencyLabel } from '../../services/zaakUrgency.js'
import OpenInApp from 'vue-material-design-icons/OpenInApp.vue'
import Folder from 'vue-material-design-icons/Folder.vue'

const URGENCY_RANK = { verlopen: 0, 'bijna-verlopen': 1, 'op-tijd': 2 }

export default {
	name: 'OpenZakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		OpenInApp,
		Folder,
	},

	data() {
		return {
			loading: false,
			zaken: [],
		}
	},

	computed: {
		/**
		 * The widget items, most-urgent-first (verlopen, then bijna-verlopen by
		 * nearest deadline, then the rest), each decorated with its urgency label
		 * and deadline date (ui-dashboard-widgets REQ-006).
		 *
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-006
		 */
		items() {
			return [...this.zaken]
				.sort((a, b) => {
					const ra = URGENCY_RANK[deriveZaakUrgency(a)] ?? 3
					const rb = URGENCY_RANK[deriveZaakUrgency(b)] ?? 3
					if (ra !== rb) {
						return ra - rb
					}
					const da = a.uiterlijkeEinddatumAfdoening ? new Date(a.uiterlijkeEinddatumAfdoening).getTime() : Infinity
					const db = b.uiterlijkeEinddatumAfdoening ? new Date(b.uiterlijkeEinddatumAfdoening).getTime() : Infinity
					return da - db
				})
				.map(zaak => {
					const urgency = deriveZaakUrgency(zaak)
					const label = urgency ? t('zaakafhandelapp', urgencyLabel(urgency)) : ''
					const deadline = zaak.uiterlijkeEinddatumAfdoening || ''
					return {
						id: zaak.id,
						mainText: zaak.identificatie,
						subText: [label, deadline].filter(Boolean).join(' · ') || zaak.zaaktype,
						avatarUrl: this.getItemIcon(),
					}
				})
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-006
		 */
		overdueCount() {
			return this.zaken.filter(zaak => deriveZaakUrgency(zaak) === 'verlopen').length
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-006
		 */
		overdueHeaderText() {
			return n('zaakafhandelapp', '%n case overdue', '%n cases overdue', this.overdueCount)
		},
	},

	mounted() {
		this.fetchZaakItems()
	},

	methods: {
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-001
		 */
		fetchZaakItems() {
			this.loading = true
			zaakStore.refreshZakenList()
				.then(() => {
					this.zaken = zaakStore.zakenList || []
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-003
		 */
		getItemIcon() {
			const theme = getTheme()

			let appLocation = '/custom_apps'

			if (window.location.hostname === 'nextcloud.local') {
				appLocation = '/apps-extra'
			}

			return theme === 'light' ? `${appLocation}/zaakafhandelapp/img/briefcase-account-outline-dark.svg` : `${appLocation}/zaakafhandelapp/img/briefcase-account-outline.svg`
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-002
		 */
		search() {
			console.info('click')
		},
		/**
		 * @spec openspec/specs/ui-dashboard-widgets/spec.md#REQ-004
		 */
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
	},

}
</script>
<style scoped>
.openZakenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
   overflow: auto;
   margin-block-end: var(--zaa-margin-10);
}
.overdueHeader {
   color: var(--color-error);
   font-weight: bold;
   margin-block-end: var(--zaa-margin-10, 8px);
}
</style>
