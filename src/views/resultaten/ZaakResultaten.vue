<script setup>
import { navigationStore, resultaatStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="filteredResultatenList?.length">
			<NcListItem v-for="(resultaat, i) in filteredResultatenList"
				:key="`${resultaat}${i}`"
				:name="resultaat?.resultaattype"
				:bold="true"
				:active="resultaatStore.resultaatItem?.id === resultaat?.id"
				:force-display-actions="true"
				@click="toggleResultaat(resultaat)">
				<template #icon>
					<FileChartCheckOutline disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ resultaat?.toelichting }}
				</template>
				<template #actions>
					<NcActionButton @click="resultaatStore.setResultaatItem(resultaat); navigationStore.setModal('resultaatForm')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
					<NcActionButton @click="resultaatStore.setResultaatItem(resultaat); navigationStore.setModal('deleteResultaat')">
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen van zaak
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!filteredResultatenList?.length && !loading">
			Geen resultaten gevonden.
		</div>

		<NcLoadingIcon v-if="!filteredResultatenList?.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Resultaten aan het laden" />
	</div>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import FileChartCheckOutline from 'vue-material-design-icons/FileChartCheckOutline.vue'

export default {
	name: 'ZaakResultaten',
	components: {
		NcListItem,
		NcActionButton,
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
		}
	},
	computed: {
		filteredResultatenList() {
			return resultaatStore.resultatenList.filter((resultaat) => resultaat.zaak === this.zaakId)
		},
	},
	watch: {
		zaakId(newVal) {
			this.fetchData()
		},
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData() {
			this.loading = true

			resultaatStore.refreshResultatenList()
				.finally(() => {
					this.loading = false
				})
		},
		toggleResultaat(resultaat) {
			if (resultaatStore.resultaatItem?.id === resultaat.id) {
				resultaatStore.setResultaatItem(null)
			} else {
				resultaatStore.setResultaatItem(resultaat)
			}
		},
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
