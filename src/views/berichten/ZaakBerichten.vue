<script setup>
import { navigationStore, berichtStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="!loading && !!filteredBerichten?.length">
			<NcListItem v-for="(bericht, i) in filteredBerichten"
				:key="`${bericht}${i}`"
				:name="bericht?.onderwerp"
				:active="berichtStore.berichtItem?.id === bericht.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="toggleBericht(bericht)">
				<template #icon>
					<ChatOutline :class="berichtStore.berichtItem?.id === bericht.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ bericht?.berichttekst }}
				</template>
				<template #actions>
					<NcActionButton @click="berichtStore.setBerichtItem(bericht); navigationStore.setSelected('berichten')">
						<template #icon>
							<Eye :size="20" />
						</template>
						Bekijken
					</NcActionButton>
					<!-- <NcActionButton @click="berichtStore.setBerichtItem(bericht); navigationStore.setModal('editBericht')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton> -->
					<NcActionButton>
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen van zaak
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!filteredBerichten?.length && !loading">
			Geen berichten gevonden.
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Berichten aan het laden" />
	</div>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'ZaakBerichten',
	components: {
		NcListItem,
		NcActionButton,
		ChatOutline,
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
		filteredBerichten() {
			return berichtStore.berichtenList.filter(bericht => zaakStore.zaakItem.berichten.includes(bericht.id))
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

			berichtStore.refreshBerichtenList()
				.finally(() => {
					this.loading = false
				})
		},
		toggleBericht(bericht) {
			if (berichtStore.berichtItem?.id === bericht.id) {
				berichtStore.setBerichtItem(null)
			} else {
				berichtStore.setBerichtItem(bericht)
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
