<script setup>
import { store } from '../../store.js'
</script>

<template>
	<div>
		<ul v-if="!loading">
			<NcListItem v-for="(taken, i) in takenList.results"
				:key="`${taken}${i}`"
				:name="taken?.name"
				:bold="true"
				:active="store.taakItem === taken?.id"
				:details="'1h'"
				:counter-number="44"
				@click="store.setTaakItem(taken.id)">
				<template #icon>
					<CalendarMonthOutline :class="store.taakItem === taken.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ taken?.summary }}
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
			name="Taken aan het laden" />
	</div>
</template>
<script>
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'

export default {
	name: 'ZaakTaken',
	components: {
		NcListItem,
		NcActionButton,
		CalendarMonthOutline,
		NcLoadingIcon,
	},
	data() {
		return {
			search: '',
			loading: true,
			takenList: [],
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData(newPage) {
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
