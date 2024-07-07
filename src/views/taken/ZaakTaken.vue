<script setup>
import { store } from '../../store.js'
</script>

<template>
	<ul>
		<NcListItem v-for="(taken, i) in takenList.results"
			v-if="!loading"
			:key="`${taken}${i}`"
			:name="taken?.name"
			:bold="true"
			:active="store.taakItem === taken?.id"
			:details="'1h'"
			:counter-number="44"
			@click="setActive(taken.id)">
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

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Taken aan het laden" />
	</ul>
</template>
<script>
import { NcListItem, NcListItemIcon, NcActionButton, NcAvatar, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import Magnify from 'vue-material-design-icons/Magnify'
// eslint-disable-next-line n/no-missing-import
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'

export default {
	name: 'ZaakTaken',
	components: {
		NcListItem,
		NcListItemIcon,
		NcActionButton,
		NcAvatar,
		NcTextField,
		CalendarMonthOutline,
		Magnify,
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
			this.loading = true,
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
		setActive(id) {
			store.setTaakItem(id);
			this.$emit('taakId', id)
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
