<script setup>
import { translate as t } from '@nextcloud/l10n'
import { zaakStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="!loading">
			<NcListItem v-for="(zaak, i) in zaakStore.zakenList"
				:key="`${zaak}${i}`"
				:name="zaak?.identificatie"
				:active="zaakStore.zaakItem?.id === zaak?.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="toggleZaak(zaak)">
				<template #icon>
					<BriefcaseAccountOutline :class="zaakStore.zaakItem?.id === zaak?.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ zaak?.zaaktype }}
				</template>
				<template #actions>
					<NcActionButton>
						Button one
					</NcActionButton>
					<NcActionButton>
						Button two
					</NcActionButton>
					<NcActionButton>
						Button three
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!zaakStore.zakenList?.length && !loading">
			{{ t('zaakafhandelapp', 'No cases found.') }}
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			:name="t('zaakafhandelapp', 'Loading cases')" />
	</div>
</template>
<script>
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'ZakenZaken',
	components: {
		NcListItem,
		NcActionButton,
		BriefcaseAccountOutline,
		NcLoadingIcon,
	},
	props: {
		zaakId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			search: '',
			loading: true,
			zakenList: [],
		}
	},
	watch: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-002
		 */
		zaakId(newVal) {
			this.fetchData(newVal)
		},
	},
	mounted() {
		this.fetchData(this.zaakId)
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-001
		 */
		fetchData() {
			this.loading = true

			zaakStore.refreshZakenList()
				.finally(() => {
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-002
		 */
		toggleZaak(zaak) {
			// TODO: toggle zaak in local component
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-003
		 */
		clearText() {
			this.search = ''
		},
	},
}
</script>
<style>
.listHeader {
    position: sticky;
    top: 0;
    z-index: 1000;
    background-color: var(--color-main-background);
    border-bottom: 1px solid var(--color-border);
}

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
</style>
