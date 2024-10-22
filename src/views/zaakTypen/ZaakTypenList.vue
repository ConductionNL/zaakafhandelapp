<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value.sync="store.search"
					:show-trailing-button="search !== ''"
					label="Search"
					trailing-button-icon="close"
					class="searchField"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="fetchData()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="store.setModal('addZaakType')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Zaaktype toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="!loading">
				<NcListItem v-for="(zaaktypen, i) in zaakTypenList.results"
					:key="`${zaaktypen}${i}`"
					:name="zaaktypen?.name"
					:active="store.zaakTypeItem === zaaktypen?.id"
					:details="'1h'"
					:counter-number="44"
					@click="store.setZaakTypeItem(zaaktypen.id)">
					<template #icon>
						<AlphaTBoxOutline :class="store.zaakTypenItem === zaaktypen.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ zaaktypen?.summary }}
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
			</div>
		</ul>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Zaken aan het laden" />
	</NcAppContentList>
</template>
<script>
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

import Magnify from 'vue-material-design-icons/Magnify.vue'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'

export default {
	name: 'ZaakTypenList',
	components: {
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		AlphaTBoxOutline,
		Magnify,
		Plus,
		Refresh,
	},
	data() {
		return {
			search: '',
			loading: true,
			zaakTypenList: [],
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData(newPage) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.zaakTypenList = data
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
