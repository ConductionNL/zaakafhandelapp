<script setup>
import { navigationStore, taakStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="!loading">
			<NcListItem v-for="(taak, i) in takenList.results"
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
					<NcActionButton @click="showEditTaakModal(taak)">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!takenList?.results?.length && !loading">
			Geen taken gevonden.
		</div>

		<NcLoadingIcon v-if="loading"
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
import Pencil from 'vue-material-design-icons/Pencil.vue'

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
			takenList: [],
		}
	},
	watch: {
		zaakId(newVal) {
			this.fetchData(newVal)
		},
	},
	mounted() {
		this.fetchData(this.zaakId)
	},
	methods: {
		showEditTaakModal(taak) {
			taakStore.setTaakItem(taak)
			navigationStore.setModal('editTaak')
		},
		fetchData(zaakId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/taken',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.takenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		toggleTaak(taak) {
			// TODO: toggle taak
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
