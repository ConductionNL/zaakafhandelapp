<script setup>
import { zaakStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul v-if="!loading">
			<NcListItem v-for="(zaak, i) in zaakStore.zakenList"
				:key="`${zaak}${i}`"
				:name="zaak?.omschrijving"
				:active="zaakStore.zaakItem?.uuid === zaak?.uuid"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="zaakStore.setZaakItem(zaak)">
				<template #icon>
					<BriefcaseAccountOutline :class="zaakStore.zaakItem?.uuid === zaak?.uuid && 'selectedZaakIcon'"
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
		</ul>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Zaken aan het laden" />
	</NcAppContentList>
</template>
<script>
import { NcListItem, NcActionButton, NcAppContentList, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'ZakenZaken',
	components: {
		NcListItem,
		NcActionButton,
		NcAppContentList,
		BriefcaseAccountOutline,
		NcLoadingIcon,
	},
	data() {
		return {
			search: '',
			loading: true,
			zakenList: [],
		}
	},
	updated() {
		this.loading = true

		zaakStore.refreshZakenList()
			.then(() => {
				this.loading = false
			})
	},
	methods: {
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
