<script setup>
import { navigationStore, besluitStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="besluiten[zaakId]?.besluiten?.length">
			<NcListItem v-for="(besluit, i) in besluiten[zaakId]?.besluiten"
				:key="`${besluit}${i}`"
				:name="besluit?.name || besluit?.besluit"
				:bold="true"
				:active="besluitStore.besluitItem?.id === besluit?.id"
				:force-display-actions="true"
				@click="toggleBesluit(besluit)">
				<template #icon>
					<BriefcaseAccountOutline disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ besluit?.summary }}
				</template>
				<template #actions>
					<NcActionButton @click="(besluitStore.zaakId = zaakId); besluitStore.setBesluitItem(besluit); navigationStore.setModal('besluitForm')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
					<NcActionButton @click="(besluitStore.zaakId = zaakId); besluitStore.setBesluitItem(besluit); navigationStore.setModal('deleteBesluit')">
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen van zaak
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!besluiten[zaakId]?.besluiten?.length && !loading">
			Geen besluiten gevonden.
		</div>

		<NcLoadingIcon v-if="!besluiten[zaakId]?.besluiten?.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Besluiten aan het laden" />
	</div>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'

export default {
	name: 'ZaakBesluiten',
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
			// this is saved in a cache like system for easier navigation between zaken and to avoid unnecessary wait time for the end user
			// eg. besluiten[zaakId] = { besluiten: [], loading: false }
			besluiten: {},
			search: '',
			loading: true,
		}
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

			besluitStore.getBesluiten(this.zaakId)
				.then(({ data }) => {
					this.besluiten[this.zaakId] = {
						besluiten: data,
						loading: false,
					}
				})
				.finally(() => {
					this.loading = false
				})
		},
		toggleBesluit(besluit) {
			if (besluitStore.besluitItem?.id === besluit.id) {
				besluitStore.setBesluitItem(null)
			} else {
				besluitStore.setBesluitItem(besluit)
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
