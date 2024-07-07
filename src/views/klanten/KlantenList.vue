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

			<NcListItem v-for="(klanten, i) in klantenList.results"
				:key="`${klanten}${i}`"
				:name="klanten?.name"
				:active="store.klantItem === klanten?.id"
				:details="'1h'"
				:counter-number="44"
				@click="setActive(klanten.id)">
				<template #icon>
					<AccountOutline :class="store.klantItem === klanten.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ klanten?.summary }}
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
			name="Klanten aan het laden" />
	</NcAppContentList>
</template>
<script>
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import Magnify from 'vue-material-design-icons/Magnify'
// eslint-disable-next-line n/no-missing-import
import AccountOutline from 'vue-material-design-icons/AccountOutline'

export default {
	name: 'KlantenList',
	components: {
		NcListItem,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		AccountOutline,
		Magnify,
		NcLoadingIcon
	},
	data() {
		return {
			search: '',
			loading: true,
			klantenList: [],
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData(newPage) {
			this.loading = true,
			fetch(
				'/index.php/apps/zaakafhandelapp/api/klanten',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.klantenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		setActive(id) {
			store.setKlantItem(id);
			this.$emit('klantId', id)
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
