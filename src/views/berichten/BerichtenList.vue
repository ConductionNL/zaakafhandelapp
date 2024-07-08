<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContentList>
		<ul v-if="!loading">
			<div class="listHeader">
				<NcTextField class="searchField"
					disabled
					:value.sync="search"
					label="Search"
					trailing-button-icon="close"
					:show-trailing-button="search !== ''"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
			</div>

			<NcListItem v-for="(berichten, i) in berichtenList.results"
				:key="`${berichten}${i}`"
				:name="berichten?.name"
				:active="store.berichtItem === berichten?.id"
				:details="'1h'"
				:counter-number="44"
				@click="store.setBerichtItem(berichten.id)">
				<template #icon>
					<ChatOutline :class="store.berichtItem === berichten.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ berichten?.summary }}
				</template>
				<template #actions>
					<NcActionButton @click="editBericht(bericht)">
						Bewerken
					</NcActionButton>
					<NcActionButton>
						Verwijderen
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
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import Magnify from 'vue-material-design-icons/Magnify'
// eslint-disable-next-line n/no-missing-import
import ChatOutline from 'vue-material-design-icons/ChatOutline'

export default {
	name: 'BerichtenList',
	components: {
		NcListItem,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		ChatOutline,
		Magnify,
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
		editBericht(bericht) {
			store.setBerichtItem(bericht)
			store.setModal('editBericht')
		},
		fetchData(newPage) {
			this.loading = true
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
