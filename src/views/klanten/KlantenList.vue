<script setup>
import { navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value.sync="search"
					:show-trailing-button="search !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="klantStore.refreshKlantenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="klantStore.setKlantItem(null); navigationStore.setModal('editKlant')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Klant toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="klantStore.klantenList">
				<NcListItem v-for="(klant, i) in klantStore.klantenList.results"
					:key="`${klant}${i}`"
					:name="fullName(klant)"
					:active="store.klantId === klant?.id"
					:force-display-actions="true"
					:details="'1h'"
					:counter-number="44"
					@click="store.setKlantItem(klant)">
					<template #icon>
						<AccountOutline :class="klantStore.klantItem === klant.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ klant?.subject }}
					</template>
					<template #actions>
						<NcActionButton @click="klantStore.setKlantItem(klant); store.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); store.setDialog('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="!klantStore.klantenList"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Klanten aan het laden" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcActions } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'KlantenList',
	components: {
		// Components
		NcListItem,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		AccountOutline,
		Magnify,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: true,
			klantenList: [],
		}
	},
	mounted() {
		klantStore.refreshKlantenList()
	},
	methods: {
		fullName(klant) {
			return klant.voorvoegsel ? `${klant.voorvoegsel} ${klant.achternaam}` : klant.achternaam
		},
		fetchData(newPage) {
			this.loading = true
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
