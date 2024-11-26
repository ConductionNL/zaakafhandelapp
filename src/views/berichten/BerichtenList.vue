<script setup>
import { navigationStore, berichtStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
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
				<NcActions>
					<NcActionButton @click="berichtStore.refreshBerichtenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="berichtStore.setBerichtItem(null); navigationStore.setModal('editBericht')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Bericht toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="berichtStore.berichtenList?.length && !loading">
				<NcListItem v-for="(bericht, i) in berichtStore.berichtenList"
					:key="`${bericht}${i}`"
					:name="bericht?.onderwerp || bericht?.title || 'onbekend'"
					:active="berichtStore.berichtItem?.id === bericht?.id"
					:details="'1h'"
					:counter-number="44"
					:force-display-actions="true"
					@click="berichtStore.setBerichtItem(bericht)">
					<template #icon>
						<ChatOutline :class="berichtStore.berichtItem?.id === bericht.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ bericht?.berichttekst }}
					</template>
					<template #actions>
						<NcActionButton @click="berichtStore.setBerichtItem(bericht); navigationStore.setModal('editBericht')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="berichtStore.setBerichtItem(bericht); navigationStore.setDialog('deleteBericht')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!berichtStore.berichtenList?.length && !loading">
			Geen berichten gedefinieerd.
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Berichten aan het laden" />
	</NcAppContentList>
</template>
<script>
//  Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'BerichtenList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		Magnify,
		Refresh,
		Plus,
		ChatOutline,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: true,
			berichtenList: [],
		}
	},
	mounted() {
		berichtStore.refreshBerichtenList().then(() => {
			this.loading = false
		})
	},
	methods: {
		editBericht(bericht) {
			berichtStore.setBerichtItem(bericht)
			navigationStore.setModal('editBericht')
		},
		storeBericht(bericht) {
			berichtStore.setBerichtItem(bericht)
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
