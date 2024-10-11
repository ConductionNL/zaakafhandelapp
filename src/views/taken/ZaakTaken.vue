<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<div>
		<ul v-if="!loading">
			<NcListItem v-for="(taak, i) in takenList.results"
				:key="`${taak}${i}`"
				:name="taak?.title"
				:bold="true"
				:active="store.taakId === taak?.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="store.setTaakId(taak.id)">
				<template #icon>
					<CalendarMonthOutline :class="store.taakId === taak.id && 'selectedZaakIcon'"
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
		</ul>

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
		zaakId: {
			handler(zaakId) {
				this.fetchData(zaakId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(store.zaakItem)
	},
	methods: {
		showEditTaakModal(taak) {
			store.setTaakItem(taak)
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
