<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContentList>
		<ul v-if="!loading">
			<NcListItem v-for="(berichten, i) in berichtenList.results"
				:key="`${berichten}${i}`"
				:name="berichten?.name"
				:active="store.berichtItem === berichten?.id"
				:details="'1h'"
				:counter-number="44"
				@click="setActive(berichten.id)">
				<template #icon>
					<ChatOutline :class="store.berichtItem === berichten.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ berichten?.summary }}
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
			name="Berichten aan het laden" />
	</NcAppContentList>
</template>
<script>
import { NcListItem, NcActionButton, NcAppContentList, NcLoadingIcon } from '@nextcloud/vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline'

export default {
	name: 'ZaakBerichten',
	components: {
		NcListItem,
		NcActionButton,
		NcAppContentList,
		ChatOutline,
		NcLoadingIcon,
	},
	data() {
		return {
			search: '',
			loading: true,
			berichtenList: [],
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData(newPage) {
			this.loading = true,
			fetch(
				'/index.php/apps/zaakafhandelapp/api/berichten',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.berichtenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		setActive(id) {
			store.setBerichtItem(id);
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
