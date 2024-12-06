<script setup>
import { navigationStore, taakStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="filteredTakenList?.length">
			<NcListItem v-for="(taak, i) in filteredTakenList"
				:key="`${taak}${i}`"
				:name="taak?.title"
				:bold="true"
				:active="taakStore.taakItem?.id === taak?.id"
				:details="taak.status"
				:counter-number="taak.deadline ? new Date(taak.deadline).toLocaleDateString() : 'no deadline'"
				:force-display-actions="true"
				@click="toggleTaak(taak)">
				<template #icon>
					<CalendarMonthOutline :class="taakStore.taakItem?.id === taak.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ taak?.onderwerp }}
				</template>
				<template #actions>
					<NcActionButton @click="taakStore.setTaakItem(taak); navigationStore.setSelected('taken')">
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

		<div v-if="!filteredTakenList?.length && !loading">
			Geen taken gevonden.
		</div>

		<NcLoadingIcon v-if="!filteredTakenList?.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Taken aan het laden" />
	</div>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'

export default {
	name: 'ZaakTaken',
	components: {
		NcListItem,
		NcActionButton,
		CalendarMonthOutline,
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
		filteredTakenList() {
			return taakStore.takenList.filter((taak) => taak.zaak === this.zaakId)
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

			taakStore.refreshTakenList()
				.finally(() => {
					this.loading = false
				})
		},
		toggleTaak(taak) {
			if (taakStore.taakItem?.id === taak.id) {
				taakStore.setTaakItem(null)
			} else {
				taakStore.setTaakItem(taak)
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
